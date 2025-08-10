<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Traits\FileSizeConverter;
use App\Models\Collection;
use App\Models\CollectionData;
use Carbon\Carbon;
use App\Traits\AuditLoggable;
use PhpParser\Node\Expr\FuncCall;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{

    use FileSizeConverter;
    use AuditLoggable;

    public function index()
    {
        return view('directory');
    }

    private function getUserKey()
    {
        $userKey = \App\UserKey::where('user_id', session()->get('userID'))->first();
        //return $userKey->getAttribute('userKey');
        //return 'f40a318be8999b1959cb2f788ea37388';
        return '19a5b5a6c93db9cc58a176e309980044';
    }

    public function manageCollection()
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'userkey' => $this->getUserKey(),
        ];

        $body = [
            'org' => 1
        ];
        // logger('Headers:', $headers);
        // logger('Body:', $body);

        $response = Http::withHeaders($headers)->asForm()->post(env('API_BASE_URL') . '/enterprise_list_all_buckets_in_s3/v1', $body);
        if ($response->successful()) {
            $buckets = $response->json();
            $data = $buckets['res'];
        } else {
            return response()->json(['error' => 'Failed to post data'], $response->status());
        }
        $local_collections = Collection::where('storage_type', 'S3')->get();

        return view('collections.collections', compact('data', 'local_collections'));
    }

    public function s3Bucket()
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'userkey' => $this->getUserKey(),
        ];

        $body = [
            'org' => 1
        ];
        // logger('Headers:', $headers);
        // logger('Body:', $body);
        $response = Http::withHeaders($headers)->asForm()->post(env('API_BASE_URL') . '/enterprise_list_all_buckets_in_s3/v1', $body);
        if ($response->successful()) {
            $buckets = $response->json();
            $data = $buckets['res'];
        } else {
            return response()->json(['error' => 'Failed to post data'], $response->status());
        }
        $local_collections = Collection::where('storage_type', 'S3')->get();

        return view('buckets', compact('data', 'local_collections'));
    }

    public function s3BucketObjects($org_id, $bucket_name)
    {

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'userkey' => $this->getUserKey(),
        ];

        $body = [
            'org' => $org_id,
            'bucket_name' => $bucket_name,
        ];
        // logger('Headers:', $headers);
        // logger('Body:', $body);

        $response = Http::withHeaders($headers)->asForm()->post(env('API_BASE_URL') . '/enterprise_list_all_objs_in_s3/v1', $body);
        if ($response->successful()) {
            $list_objects = $response->json();
            $data = $list_objects['res'];
        } else {
            return response()->json(['error' => 'Failed to post data'], $response->status());
        }
        return view('s3details', compact('data'));
    }

    public function getCollections(Request $request)
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'userkey' => $this->getUserKey(),
        ];

        $body = [
            'org' => $request->org_id,
            'source_type' => $request->storage_type
        ];
        $end_point = '/enterprise_list_all_containers_in_source/v1';

        // Use JSON payload instead of `asForm`
        $response = Http::withHeaders($headers)
            ->asForm()
            ->post(env('API_BASE_URL') . $end_point, $body);

        $body['end_point'] = $end_point;

        $message = '';
        $state = 'success';
        $data = [];
        $statusCode = $response->status();
        if ($response->body() === 'null') {
            return response()->json(['data' => [], 'state' => 'success'], $response->status());
        } elseif ($response->successful() && $response->status() == 200) {
            $buckets = $response->json();
            if (is_array($buckets)) {
                sort($buckets, SORT_STRING | SORT_FLAG_CASE);
            }
            $data = $buckets;
        } else {
            $responseBody = $response->json();
            $message = $responseBody['detail']['message'] ?? $responseBody;
            $state = 'error';
            $statusCode = 200;
        }

        return response()->json(['data' => $data, 'state' => $state, 'message' => $message], $statusCode);
    }

    public function getCollectionsOld(Request $request)
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'userkey' => $this->getUserKey(),
        ];

        $body = [
            'org' => $request->org_id
        ];

        $actionName = '';
        if (strtolower($request->storage_type) === strtolower('sharepoint')) {
            $end_point = '/enterprise_list_all_sites_in_sharepoint/v1';
            $actionName = 'LIST_SITES';
        } else {
            $end_point = '/enterprise_list_all_buckets_in_s3/v1';
            $actionName = 'LIST_BUCKETS';
        }

        $response = Http::withHeaders($headers)->asForm()->post(env('API_BASE_URL') . $end_point, $body);

        $body['end_point'] = $end_point;

        if ($response->body() === 'null') {
            return response()->json(['data' => [], 'state' => 'success'], status: $response->status());
        } elseif ($response->body() !== 'null' && $response->successful() && $response->status() == 200) {
            $buckets = $response->json();
            $data = $buckets['res'];
        } else {
            return response()->json(['error' => 'Failed to post data'], $response->status());
        }
        return response()->json(['data' => $data, 'state' => 'success'], status: $response->status());
    }




    public function getLocalBucketItems1(Request $request)
    {
        $collection = Collection::find($request->collection_id);
        $files = CollectionData::where('collection_id', $request->collection_id)->get();
        return view('collections.local_collection_details', compact('collection', 'files'));
    }

    public function getLocalBucketItems(Request $request)
    {
        $collection = Collection::find($request->collection_id);

        // Format each file's date
        $files = CollectionData::where('collection_id', $request->collection_id)->get();

        return view('collections.local_collection_details', compact('collection', 'files'));
    }


    private function getCountainerParamaters($request)
    {
        $container = [];

        switch ($request->serviceprovider) {
            case 'S3':
                $container = json_encode(['bucket_name' => $request->bucketName]);
                break;
            case 'SharePoint':
                $container = json_encode(['sharepoint_site' => $request->bucketName]);
                break;
            case 'MFiles':
                $container = json_encode(['vault' => $request->bucketName]);
                break;
        }

        return $container;
    }


    /**
     * Retrieve and organize items from a bucket or SharePoint site.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBucketItems(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'org_id' => 'required',
                'serviceprovider' => 'required',
                'bucketName' => 'required'
            ]);

            // Set up headers
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'userkey' => $this->getUserKey(),
            ];
            $container = $this->getCountainerParamaters($request);
            // Prepare request body
            $body = [
                'org' => $request->org_id,
                'source_type' => $request->serviceprovider,
                'container' => $container
            ];

            $end_point = '/enterprise_list_all_objs_in_source/v1';

            // Make the API request
            $response = Http::withHeaders($headers)
                ->asForm()
                ->post(env('API_BASE_URL') . $end_point, $body);


            // Handle non-200 responses
            if (!$response->successful()) {
                return response()->json([
                    'state' => 'error',
                    'message' => 'Failed to fetch data',
                    'data' => null
                ], $response->status());
            }

            $responseData = $response->json();

            // Initialize filesByFolder with root
            $filesByFolder = ['root' => []];

            // Process each file
            foreach ($responseData as $item) {
                if (!isset($item['file'])) {
                    continue;
                }

                // Add formatted last modified date
                $item['file']['last_modified_readable'] = Carbon::parse($item['file']['lastModifiedDateTime'])
                    ->format('m-d-Y H:i');

                // All files go to root folder as per example
                $filesByFolder['root'][] = [
                    'file' => [
                        'name' => $item['file']['name'],
                        'size' => $item['file']['size'],
                        'lastModifiedDateTime' => $item['file']['lastModifiedDateTime'],
                        'last_modified_readable' => $item['file']['last_modified_readable']
                    ],
                    'vault' => $request->bucketName
                ];
            }
            // Sort the root folder files by name
            usort($filesByFolder['root'], function ($a, $b) {
                return strcmp($a['file']['name'], $b['file']['name']);
            });
            // Prepare final response
            return response()->json([
                'state' => 'success',
                'data' => [
                    'filesByFolder' => $filesByFolder,
                    'collection_name' => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->bucketName))
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('getBucketItems error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'state' => 'error',
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }


    public function getCollectionNew(Request $request)
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'userkey' => $this->getUserKey(),
        ];

        $body = [
            'org' => 1
        ];
        $end_point = '/enterprise_list_all_buckets_in_s3/v1';
        $response = Http::withHeaders($headers)->asForm()->post(env('API_BASE_URL') . $end_point, $body);
        return response()->json($response->json());
        if ($response->json() === null) {
            return response()->json(['message' => 'No data recived from api'], status: 500);
        } elseif ($response->status() == 200 && $response->successful()) {
            $buckets = $response->json();
            $data = $buckets['res'];
        } else {
            return response()->json(['error' => 'Failed to post data'], $response->status());
        }

        return view('collections.cloudcollection', compact('data'));
    }

    public function publishCollection(Request $request)
    {
        $publishedCollectionName = $request->published_collection_name == 'null' ? null : $request->published_collection_name;

        $collectionExist = false; // Default value

        if (!empty($publishedCollectionName)) {
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'userkey' => $this->getUserKey(),
            ];

            $end_point = '/list_collections_of_org/v1';

            $body = [
                'org' => $request->org_id,
            ];

            $response = Http::withHeaders($headers)
                ->asForm()
                ->post(env('API_BASE_URL') . $end_point, $body);

            if ($response->successful() && $response->body() != 'null' && $response->status() == 200) {
                $data = $response->json();
                // Check if $publishedCollectionName exists in collection_name field
                foreach ($data as $collection) {
                    if ($collection['collection_name'] === $publishedCollectionName) {
                        $collectionExist = true;
                        break;
                    }
                }
            }
        }

        try {
            $isRenamed = false;
            if ((!empty($publishedCollectionName) && $publishedCollectionName != $request->collection_name) || $collectionExist) {
                $headers = [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'userkey' => $this->getUserKey(),
                ];
                $body = [
                    'org' => $request->org_id,
                    'collection_name' => $publishedCollectionName
                ];

                $end_point = '/delete_collection_of_org/v1';
                $response = $this->deleteCloudeClollection($headers,  $body, $end_point);

                if (!$response->successful() && $response->body() == 'null') {
                    // Handle failed response
                    return response()->json([
                        'error' => 'Request failed',
                        'status' => $response->status(),
                        'message' => $response->body(), // optional: display the response body
                    ], $response->status());
                }
                $isRenamed = true;
            }

            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'userkey' => $this->getUserKey(),
            ];

            $end_point = '/enterprise_create_collection_from_source/v1';

            $body = [
                'org' => $request->org_id,
                'collection_name' => $request->collection_name,
                'folders_or_files_from_different_containers' => json_encode($request->file_details)
            ];
            $response = Http::withHeaders($headers)
                ->asForm()
                ->post(env('API_BASE_URL') . $end_point, $body);

            $statusCode = $response->status();

            if ($response->successful() && $response->body() != 'null' && $response->status() == 200) {
                $data = $response->json();
                $message = $data;
                $request->file_details;
                $file_detials = $this->convertArrayToString($request->file_details, $request->collection_name, $request->org_id, $request->storage_type, $isRenamed, $publishedCollectionName);

                $this->logAudit(
                    actionName: 'PUBLISH_COLLECTION',
                    oldData: null,
                    newData: null,
                    action_description: $file_detials,
                    actionType: 'PUBLISH'
                );

                Collection::where('id', $request->db_collection_id)
                    ->update([
                        'is_synced' => 1,
                        'is_cloud_collection' => 1,
                        'published_collection_name' => $request->collection_name
                    ]);
                // Handle successful response
                return response()->json([
                    'status' => 'success', // or 'error' based on the scenario
                    'message' => $message,
                    'data' => $response->body(), // Using the resource for structured data
                ]); // or process the response data as needed
            } else {
                // Handle failed response
                $data = json_decode($response->body(), true);
                return response()->json([
                    'error' => 'Request failed',
                    'status' => $response->status(),
                    'message' => $data['detail']['message'], // optional: display the response body
                ], $response->status());
            }
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json([
                'error' => 'An exception occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
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


    public function deleteCloudeClollection($headers,  $body, $end_point)
    {
        return Http::withHeaders($headers)->asForm()->post(env('API_BASE_URL') . $end_point, $body);
    }


    public function deleteCollection(Request $request)
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'userkey' => $this->getUserKey(),
        ];
        $body = [
            'org' => $request->org_id,
            'collection_name' => $request->collection_name,
        ];

        $end_point = '/delete_collection_of_org/v1';
        $response = $this->deleteCloudeClollection($headers,  $body, $end_point);
        if ($response->successful() && $response->body() != 'null' && $response->status() == 200) {
            $file_detials = "Deleted the collection '$request->collection_name'";
            $this->logAudit(
                actionName: 'DELETE_COLLECTION',
                oldData: null,
                newData: null,
                action_description: $file_detials,
                actionType: 'DELETE'
            );
            // Handle successful response
            $data = $response->json();
            $message = $data['res'];
            return response()->json([
                'status' => 'success', // or 'error' based on the scenario
                'message' => $message,
                'data' => $response->body(), // Using the resource for structured data
            ]);
        } else {
            // Handle failed response
            return response()->json([
                'error' => 'Request failed',
                'status' => $response->status(),
                'message' => $response->body(), // optional: display the response body
            ], $response->status());
        }
    }

    public function getSystemSourceTypes(Request $request)
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'userkey' => $this->getUserKey(),
        ];
        $orgId = $request->orgId;
         $body = [
            'org' =>  $orgId
        ];
        $end_point = '/get_system_source_types/v1';
        $response = Http::withHeaders($headers)->asForm()->post(env('API_BASE_URL') . $end_point, $body);
        $message = '';
        $state = 'success';
        $data = [];
        $statusCode = $response->status();
        if ($response->body() === 'null') {
            return response()->json(['data' => [], 'state' => 'success'], $response->status());
        } elseif ($response->successful() && $response->status() == 200) {
            $buckets = $response->json();
            if (!empty($buckets)) {
                usort($buckets, function ($a, $b) {
                    return strcmp($a['value'], $b['value']);
                });
            }
            $data = $buckets;
        } else {
            $responseBody = $response->json();
            $message = $responseBody['detail']['message'] ?? $responseBody;
            $state = 'error';
            $statusCode = 200;
        }

        return response()->json(['data' => $data, 'state' => $state, 'message' => $message], $statusCode);
    }

    public function groupFilesByDynamicKey(array $collections, string $groupingKey)
    {
        $grouped = [];

        foreach ($collections as $collection) {
            foreach ($collection['files'] as $file) {
                if (isset($file[$groupingKey])) {
                    $key = $file[$groupingKey];
                } else {
                    continue; // Skip if the specified key doesn't exist
                }

                // Initialize the group if it doesn't exist
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [];
                }

                // Add the file to the corresponding group
                $grouped[$key][] = [
                    'collection_name' => $collection['collection_name'],
                    'collectionCreatedDate' => $collection['collectionCreatedDate'],
                    'file' => $file['file'],
                ];
            }
        }

        return $grouped;
    }



    /**
     * Sync published collections from API and update database
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function syncPublishedCollections(Request $request)
    {
        try {
            // Get organization ID and storage type from request
            $orgId = $request->input('org_id');

            // Fetch data from API
            $apiData = $this->fetchPublishedCollectionsFromAPI($orgId);
            if (empty($apiData)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No published collections found or API error'
                ], 404);
            }

            $syncStats = [
                'collections_processed' => 0,
                'collections_created' => 0,
                'collections_updated' => 0,
                'files_processed' => 0,
                'files_created' => 0,
                'files_updated' => 0
            ];

            // Use database transaction for data consistency
            DB::transaction(function () use ($apiData, &$syncStats) {
                foreach ($apiData as $organizationId => $collections) {
                    $this->processOrganizationCollections($organizationId, $collections, $syncStats);
                }
            });

            // $this->logAudit(
            //     actionName: 'SYNC_PUBLISHED_COLLECTIONS',
            //     oldData: null,
            //     newData: null,
            //     action_description: "Synchronized published collections - Processed: {$syncStats['collections_processed']} collections, {$syncStats['files_processed']} files",
            //     actionType: 'SYNC'
            // );

            return response()->json([
                'status' => 'success',
                'message' => 'Published collections synchronized successfully',
                'stats' => $syncStats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error synchronizing published collections: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch published collections data from external API
     *
     * @param int $orgId
     * @param string $storageType
     * @return array
     */
    private function fetchPublishedCollectionsFromAPI($orgId)
    {
        try {
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'userkey' => $this->getUserKey(),
            ];

            $body = [
                'org' => $orgId
            ];

            $response = Http::withHeaders($headers)
                ->timeout(60)
                ->post(env('API_BASE_URL') . '/list_collections_of_org/v1', $body);

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new \Exception('API request failed with status: ' . $response->status());
            }

        } catch (\Exception $e) {
            \Log::error('Failed to fetch published collections from API: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process collections for a single organization efficiently
     *
     * @param int $organizationId
     * @param array $collections
     * @param array &$syncStats
     */
    private function processOrganizationCollections($organizationId, $collections, &$syncStats)
    {
        // Batch fetch existing collections to avoid N+1 queries
        $collectionNames = array_column($collections, 'collection_name');
        $existingCollections = Collection::where('organization_id', $organizationId)
            ->whereIn('collection_name', $collectionNames)
            ->get()
            ->keyBy(function ($collection) {
                return $collection->storage_type . '_' . $collection->collection_name;
            });

        $collectionsToCreate = [];
        $collectionsToUpdate = [];
        $allFilesToProcess = [];

        foreach ($collections as $collectionData) {
            $syncStats['collections_processed']++;

            $collectionName = $collectionData['collection_name'];
            $storageType = $this->determineStorageType($collectionData);
            $key = $storageType . '_' . $collectionName;

            if (isset($existingCollections[$key])) {
                // Prepare for batch update
                $collectionsToUpdate[] = [
                    'id' => $existingCollections[$key]->id,
                    'is_cloud_collection' => 1,
                    'updated_at' => now()
                ];
                $syncStats['collections_updated']++;
                $collectionId = $existingCollections[$key]->id;
            } else {
                // Prepare for batch insert
                $newCollection = [
                    'organization_id' => $organizationId,
                    'storage_type' => $storageType,
                    'collection_name' => $collectionName,
                    'published_collection_name' => $collectionName,
                    'is_synced' => 1,
                    'is_cloud_collection' => 1,
                    'created_at' => $collectionData['collectionCreatedDate'],
                    'updated_at' => now()
                ];
                $collectionsToCreate[] = $newCollection;
                $syncStats['collections_created']++;
                $collectionId = null; // Will be set after batch insert
            }

            // Prepare files for processing
            if (isset($collectionData['files']) && is_array($collectionData['files'])) {
                $allFilesToProcess[] = [
                    'collection_key' => $key,
                    'collection_id' => $collectionId,
                    'files' => $collectionData['files']
                ];
            }
        }

        // Batch create collections
        if (!empty($collectionsToCreate)) {
            Collection::insert($collectionsToCreate);

            // Get the newly created collection IDs
            $newCollections = Collection::where('organization_id', $organizationId)
                ->whereIn('collection_name', array_column($collectionsToCreate, 'collection_name'))
                ->get()
                ->keyBy(function ($collection) {
                    return $collection->storage_type . '_' . $collection->collection_name;
                });

            // Update collection IDs for file processing
            foreach ($allFilesToProcess as &$fileGroup) {
                if (is_null($fileGroup['collection_id']) && isset($newCollections[$fileGroup['collection_key']])) {
                    $fileGroup['collection_id'] = $newCollections[$fileGroup['collection_key']]->id;
                }
            }
        }

        // Batch update collections
        if (!empty($collectionsToUpdate)) {
            foreach ($collectionsToUpdate as $updateData) {
                Collection::where('id', $updateData['id'])->update($updateData);
            }
        }

        // Process all files in batches
        $this->processBatchFiles($allFilesToProcess, $syncStats);
    }

    /**
     * Process files in batches for better performance
     *
     * @param array $allFilesToProcess
     * @param array &$syncStats
     */
    private function processBatchFiles($allFilesToProcess, &$syncStats)
    {
        $filesToCreate = [];
        $filesToUpdate = [];

        foreach ($allFilesToProcess as $fileGroup) {
            if (is_null($fileGroup['collection_id'])) continue;

            $collectionId = $fileGroup['collection_id'];
            $files = $fileGroup['files'];

            // Get existing files for this collection
            $existingFiles = CollectionData::where('collection_id', $collectionId)
                ->get()
                ->keyBy(function ($file) {
                    return $file->file_name . '_' . $file->bucket_sp_site_name;
                });

            foreach ($files as $fileData) {
                $syncStats['files_processed']++;

                $file = $fileData['file'];
                $fileName = $file['name'];
                $fileSize = $file['size'];
                $lastModified = $file['collectionUsedFileDate'];
                $bucketSiteName = $this->getBucketSiteName($fileData);
                $fileKey = $fileName . '_' . $bucketSiteName;

                if (isset($existingFiles[$fileKey])) {
                    // Prepare for update
                    $filesToUpdate[] = [
                        'id' => $existingFiles[$fileKey]->id,
                        'size' => $fileSize,
                        'last_modified' => $lastModified,
                        'updated_at' => now()
                    ];
                    $syncStats['files_updated']++;
                } else {
                    // Prepare for insert
                    $sanitizedFileName = $this->sanitizeFileName($fileName);
                    $sanitizedBucketName = $this->sanitizeFileName($bucketSiteName ?? '');
                    $filesToCreate[] = [
                        'collection_id' => $collectionId,
                        'file_name' => $fileName,
                        'size' => $fileSize,
                        'bucket_sp_site_name' => $bucketSiteName,
                        'file_id' =>  $sanitizedBucketName. '-' . $sanitizedFileName,
                        'last_modified' => $lastModified,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    $syncStats['files_created']++;
                }
            }
        }

        // Batch insert files (in chunks to avoid memory issues)
        if (!empty($filesToCreate)) {
            $chunks = array_chunk($filesToCreate, 500); // Process 500 files at a time
            foreach ($chunks as $chunk) {
                CollectionData::insert($chunk);
            }
        }

        // Batch update files
        if (!empty($filesToUpdate)) {
            foreach ($filesToUpdate as $updateData) {
                CollectionData::where('id', $updateData['id'])->update($updateData);
            }
        }
    }

    /**
     * Determine storage type from collection data
     *
     * @param array $collectionData
     * @return string
     */
    private function determineStorageType($collectionData)
    {
        if (empty($collectionData['files'])) {
            return 'S3'; // Default fallback
        }

        $firstFile = $collectionData['files'][0];

        if (isset($firstFile['bucket'])) {
            return 'S3';
        } elseif (isset($firstFile['sharepoint_site'])) {
            return 'SharePoint';
        } elseif (isset($firstFile['vault'])) {
            return 'MFiles';
        }

        return 'S3'; // Default fallback
    }

    /**
     * Get bucket or site name from file data
     *
     * @param array $fileData
     * @return string|null
     */
    private function getBucketSiteName($fileData)
    {
        if (isset($fileData['bucket'])) {
            return $fileData['bucket'];
        } elseif (isset($fileData['sharepoint_site'])) {
            return $fileData['sharepoint_site'];
        } elseif (isset($fileData['vault'])) {
            return $fileData['vault'];
        }

        return null;
    }

    /**
     * Sanitize filename by replacing non-alphanumeric characters with dashes
     *
     * @param string $fileName
     * @return string
     */
    private function sanitizeFileName($fileName)
    {
        // Replace non-alphanumeric characters with dashes
        $sanitized = preg_replace('/[^a-zA-Z0-9\-_]+/', '-', $fileName);

        // Remove consecutive dashes and trim dashes from the beginning and end
        $sanitized = preg_replace('/-+/', '-', $sanitized);
        $sanitized = strtolower(trim($sanitized, '-'));

        return $sanitized;
    }
    public function getCollectionDetails(Request $request)
    {
        $collection = Collection::find($request->collection_id);
        if (!$collection) {
            return response()->json(['error' => 'Collection not found'], 404);
        }

        $files = CollectionData::where('collection_id', $request->collection_id)->get();
        $formattedFiles = $files->map(function ($file) {
            return [
                'id' => $file->id,
                'file_name' => $file->file_name,
                'size' => $this->convertFileSize($file->size),
                'last_modified' => Carbon::parse($file->last_modified)->format('Y-m-d H:i:s'),
                'bucket_sp_site_name' => $file->bucket_sp_site_name,
            ];
        });

        return response()->json([
            'collection' => [
                'id' => $collection->id,
                'name' => $collection->collection_name,
                'created_at' => Carbon::parse($collection->created_at)->format('Y-m-d H:i:s'),
                'storage_type' => $collection->storage_type,
            ],
            'files' => $formattedFiles
        ]);
    }
}
