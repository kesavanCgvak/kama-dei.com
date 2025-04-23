<?php

namespace App\Http\Controllers;

use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use App\Models\CollectionData;
use Illuminate\Http\Request;
use App\Traits\AuditLoggable;

class CollectionController extends Controller
{
    use AuditLoggable;
    public function index()
    {
        $collections = Collection::all();

        // Convert the collection to an array
        $collectionsArray = $collections->toArray();

        return $collections;
    }

    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'organization_id' => 'required|integer',
            'storage_type' => 'required',
            'collection_name' => 'required|string|max:255',
        ]);
        $validatedData['is_synced'] = 0;
        $validatedData['collection_description'] = $request->collection_description;
        // Check if the collection_name already exists for the given storage_type
        // Find existing collection by collection_name and storage_type
        $collection = Collection::where('storage_type', $validatedData['storage_type'])
            ->where('collection_name', $validatedData['collection_name'])
            ->first();

        if ($collection) {
            // If collection exists, update it
            $collection->update($validatedData);
            return response()->json([
                'status' => 'success',
                'message' => 'Collection updated successfully',
                'data' => $collection
            ]);
        } else {
            // If no existing collection, create a new one
            $collection = Collection::create($validatedData);

            $this->logAudit(
                actionName: 'CREATE_COLLECTION',
                oldData: null,
                newData: null,
                action_description: "Created the collection '$collection->collection_name' with the org id $collection->organization_id and the storage type '$collection->storage_type'",
                actionType: 'CREATE'
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Collection created successfully',
                'data' => $collection
            ]);
        }
    }

    public function checkCollection(Request $request)
    {
        $collection_id = $request->collection_id;

        $query = Collection::where('collection_name', $request->collection_name)
            ->where('storage_type', $request->storage_type);

        // Exclude the current collection_id if provided
        if (!is_null($collection_id)) {
            $query->where('id', '!=', $collection_id);
        }

        $collection = $query->first();

        return response()->json([
            'status' => 'success',
            'message' => $collection ? 'Collection exists' : 'Collection does not exist',
            'data' => $collection
        ]);
    }


    public function show($id)
    {
        $collection = Collection::findOrFail($id);
        return $collection;
    }

    public function update(Request $request, $id)
    {
        $collection = Collection::findOrFail($id);
        $oldData = $collection->toArray();
        $data = ['is_synced' => $request->is_synced, 'is_cloud_collection' => $request->is_cloud_collection];
        $collection->update($data);

        return response()->json([
            'status' => 'success', // or 'error' based on the scenario
            'message' => 'Collections updated successfully',
            'data' => $request->all() // Using the resource for structured data
        ]);
    }

    public function destroy(Request $request)
    {
        $collection = Collection::findOrFail($request->collection_id);
        // Use the relationship to delete related data if defined
        if ($collection->collectionData()->exists()) {
            $collection->collectionData()->delete();
        }
        // Delete the collection
        $collection->delete();

        return response()->json([
            'status' => 'success', // or 'error' based on the scenario
            'message' => 'Collections deleted successfully',
            'data' => '' // Using the resource for structured data
        ]);
    }


    /**
     * Retrieves local collections based on the given storage type.
     *
     * If 'All' is passed as the storage type, all collections are returned.
     * Otherwise, collections with the given storage type are returned.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getLocalCollections(Request $request)
    {
        // Fetch the collections with the required conditions
        $local_collections = Collection::where('storage_type', $request->input('storage_type'))
            ->where('organization_id', $request->input('orgID'))
            ->with(['collectionData' => function ($query) {
                $query->orderBy('file_name', 'asc');
            }])
            ->orderBy('collection_name', 'asc')
            ->get();

        // Wrap the collection in the resource
        $collectionResource = CollectionResource::collection($local_collections);

        // Return a JSON response with status and data
        return response()->json([
            'status' => 'success',
            'message' => 'Collections retrieved successfully',
            'data' => $collectionResource,
        ]);
    }


    public function deleteLocalFile(Request $request)
    {
        // return response()->json($request->id);
        $collection = CollectionData::findOrFail($request->id);
        $collectionArray = $collection->toArray();
        CollectionData::destroy($request->id);

        Collection::where('id', $collection->collection_id)->update(['is_synced' => 0]);

        $this->logAudit(
            actionName: 'DELETE_FILE',
            oldData: null,
            newData: null,
            action_description: "Deleted the file " . $collection->file_name . " from the collection " . $request->collectionName,
            actionType: 'DELETE'
        );

        return response()->json([
            'status' => 'success', // or 'error' based on the scenario
            'message' => 'File Deleted successfully',
            'data' => '', // Using the resource for structured data
        ]);
    }

    public function updateLocalFiles(Request $request)
    {
        $collectionFiles = $request->data; // All incoming data to be updated

        foreach ($collectionFiles as $file) {
            CollectionData::where('id', $file['id'])->update(['last_modified' => $file['last_modified']]);
        }

        // Retrieve the related collection IDs based on the updated CollectionData
        $collectionIds = CollectionData::whereIn('id', array_column($collectionFiles, 'id'))
            ->pluck('collection_id')
            ->unique(); // Get unique collection IDs

        // Update the is_synced field in the Collection table for the affected collections
        Collection::whereIn('id', $collectionIds)->update(['is_synced' => 0]);

        // Return the response
        return response()->json([
            'status' => 'success',
            'message' => "Record updated successfully.",
        ]);
    }

    public function storeLocalIems(Request $request)
    {

        $localItem = CollectionData::create([
            'collection_id' => $request->collection_id,
            'file_name' => $request->file_name,
            'size' => $request->size,
            'file_id' => $request->file_id,
            'last_modified' => $request->last_modified,
            'bucket_sp_site_name' => $request->bucket_name
        ]);
        Collection::where('id', $request->collection_id)->update(['is_synced' => 0]);

        $this->logAudit(
            actionName: 'CREATE_FILE',
            oldData: null,
            newData: null,
            action_description: "Created the file " . $request->file_name . " on the collection " . $request->collection_name,
            actionType: 'CREATE'
        );

        return response()->json(['status' => 'success', 'data' => $localItem]);
    }

    public function renameDbCollection(Request $request)
    {
        // Fetch the current state of the collection
        $oldData = Collection::find($request->collection_id);
        if (!$oldData) {
            return response()->json([
                'status' => 'error',
                'message' => "Collection not found."
            ], 404);
        }
        $oldDataArray = $oldData->toArray();
        // Update the collection
        $updated = $oldData->update([
            'collection_name' => $request->collection_name,
            'collection_description' => $request->collection_description,
            'is_synced' => 0
        ]);

        if ($updated) {
            // Fetch the updated state of the collection
            $newData = Collection::find($request->collection_id);
            $collection_data = CollectionData::where('collection_id', $request->collection_id)->pluck('file_name');

            $oldCollectionName = $oldDataArray['collection_name'];
            $updatedCollectionName = $newData->collection_name;
            $collectionDataFiles = $collection_data->implode(',');

            $this->logAudit(
                actionName: 'RENAME_COLLECTION',
                oldData: null,
                newData: null,
                action_description: "Renamed the collection '$oldCollectionName' to '$updatedCollectionName' with the org id $newData->organization_id, with the files '$collectionDataFiles'",
                actionType: 'RENAME'
            );

            return response()->json([
                'status' => 'success',
                'message' => "Collection renamed successfully.",

            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => "Failed to rename the collection."
        ], 500);
    }

    public function updateCollectionNote(Request $request)
    {
        $collection = Collection::findOrFail($request->collection_id);
        $collection->update(['collection_description' => $request->collection_description]);
        return response()->json([
            'status' => 'success',
            'message' => 'Collection note updated successfully',
            'data' => $request->all()
        ]);
    }

    public function copyCollection(Request $request)
    {
        // Create the Collection
        $collection = Collection::create([
            'organization_id' => $request->organization_id,
            'storage_type' => $request->storage_type,
            'collection_name' => $request->collection_name,
            'collection_description' => $request->collection_description,
            'published_collection_name' => $request->collection_name, // You can modify this as needed
            'is_synced' => 0, // Set as required
            'is_cloud_collection' => 0, // Set as required
        ]);

        if (!empty($request->collection_data)) {
            // Iterate over the collection data and create related CollectionData
            foreach ($request->collection_data as $data) {
                $collection->collectionData()->create([
                    'file_name' => $data['file_name'],
                    'size' => $data['size'],
                    'file_id' => $data['file_id'],
                    'last_modified' => $data['last_modified'],
                    'bucket_sp_site_name' => $data['bucket_sp_site_name'],
                ]);
            }
            // Load the related collection data and return the response
            $collection->load('collectionData'); // Eager load the collectionData relationship
        }


        return response()->json([
            'status' => 'success',
            'message' => 'Collection created successfully!',
            'collection' => $collection,
        ], 201);
    }


    function convertArrayToString(array $data, string $collectionName, int $orgId, string $storageType, bool $isRenamed, $publishedCollectionName): string
    {
        $output = $isRenamed ? "Renamed the collection '$publishedCollectionName' to '$collectionName' " : "Pubished collection '$collectionName'  ";
        $output .=  "with the org id $orgId, ";
        $storageMapping = [
            "S3" => ["fileIndex" => "data_folders_in_s3", "bucketKey" => "bucket_name"],
            "SharePoint" => ["fileIndex" => "folders_or_files_in_sharepoint", "bucketKey" => "sharepoint_site"],
            "MFiles" => ["fileIndex" => "folders_or_files_in_vault", "bucketKey" => "vault"]
        ];

        $selectedStorage = $storageMapping[$storageType] ?? ["fileIndex" => "data_folders_in_s3", "bucketKey" => "bucket_name"];
        $fileIndex = $selectedStorage["fileIndex"];

        $result = [];
        foreach ($data as $bucket) {
            $bucketName = $bucket[$selectedStorage["bucketKey"]];
            $files = implode(", ", $bucket[$fileIndex]);
            $result[] = "with the bucket '$bucketName' with the files '$files'";
        }
        return $output . implode(' and ', $result);
    }
}
