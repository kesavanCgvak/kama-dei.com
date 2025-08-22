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
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{

    use FileSizeConverter;
    use AuditLoggable;
    public const int TIME_OUT = 120;
    public function index()
    {
        return view('directory');
    }

    private function getUserKey()
    {
        $userKey = \App\UserKey::where('user_id', session()->get('userID'))->first();
        //return $userKey->getAttribute('userKey');
        //return 'f40a318be8999b1959cb2f788ea37388';
        return '65ebad1d278b1f961429c38d58fa0eaf';
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

        $response = Http::withHeaders($headers)
        ->asForm()
        ->timeout(self::TIME_OUT)
        ->post(env('API_BASE_URL') . '/enterprise_list_all_buckets_in_s3/v1', $body);
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
        $response = Http::withHeaders($headers)
            ->asForm()
            ->timeout(self::TIME_OUT)
            ->post(env('API_BASE_URL') . '/enterprise_list_all_buckets_in_s3/v1', $body);
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

        $response = Http::withHeaders($headers)
            ->asForm()
            ->timeout(self::TIME_OUT)
            ->post(env('API_BASE_URL') . '/enterprise_list_all_objs_in_s3/v1', $body);
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
            ->timeout(self::TIME_OUT)
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

        $response = Http::withHeaders($headers)
            ->asForm()
            ->timeout(self::TIME_OUT)
            ->post(env('API_BASE_URL') . $end_point, $body);

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
                ->timeout(self::TIME_OUT)
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

                // No formatted date; keep original value as returned by API
                $filesByFolder['root'][] = [
                    'file' => [
                        'name' => $item['file']['name'],
                        'size' => $item['file']['size'],
                        'lastModifiedDateTime' => $item['file']['lastModifiedDateTime'],
                    ],
                    'vault' => $request->bucketName
                ];
            }
            // Sort the root folder files by name
            usort($filesByFolder['root'], function ($a, $b) {
                return strcmp($a['file']['name'], $b['file']['name']);
            });

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
        $response = Http::withHeaders($headers)
            ->asForm()
            ->timeout(self::TIME_OUT)
            ->post(env('API_BASE_URL') . $end_point, $body);
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
                ->timeout(self::TIME_OUT)
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
                ->timeout(self::TIME_OUT)
                ->post(env('API_BASE_URL') . $end_point, $body);

            $statusCode = $response->status();
            $responseBody = $response->body();
            if ($response->successful() && $response->body() != 'null' && $response->status() == 200) {
                $data = $response->json();

                $request->file_details;
                $file_detials = $this->convertArrayToString($request->file_details, $request->collection_name, $request->org_id, $request->storage_type, $isRenamed, $publishedCollectionName);
                $responseData = json_decode($responseBody);
                $message = $responseData->res;
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
                        'published_collection_name' => $request->collection_name,
                        'collection_id' => $responseData->collection_id,
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
        return Http::withHeaders($headers)
            ->asForm()
            ->timeout(self::TIME_OUT)
            ->post(env('API_BASE_URL') . $end_point, $body);
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
            $this->deleteCollectionAndData($request->collection_id);
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
        $response = Http::withHeaders($headers)
            ->asForm()
            ->timeout(self::TIME_OUT)
            ->post(env('API_BASE_URL') . $end_point, $body);
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


    private function deleteCollectionAndData($collectionId)
    {
        // Use the relationship to delete related data if defined
        $collection = Collection::findOrFail($collectionId);
        // Use the relationship to delete related data if defined
        if ($collection->collectionData()->exists()) {
            $collection->collectionData()->delete();
        }
        // Delete the collection
        $collection->delete();
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
            $orgId = (int) $request->input('org_id');
            if (!$orgId) {
                return response()->json(['status' => 'error', 'message' => 'org_id is required'], 422);
            }

            $apiData = $this->fetchPublishedCollectionsFromAPI($orgId);
            if (empty($apiData)) {
                return response()->json(['status' => 'success', 'message' => 'No collections returned from API', 'stats' => ['collections_processed' => 0]]);
            }

            $statsCollections = [];
            \DB::transaction(function () use ($orgId, $apiData, &$statsCollections) {
                $statsCollections = $this->processOrganizationCollections($orgId, $apiData);
            });

            // Sync files per collection (outside or inside the same transaction as you prefer)
            $statsFiles = $this->syncFilesForCollections($orgId, $apiData);

            return response()->json([
                'status' => 'success',
                'message' => 'Collections and files synchronized successfully',
                'stats' => array_merge($statsCollections, $statsFiles)
            ]);

        } catch (\Exception $e) {
            Log::error('Sync collections error', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error synchronizing published collections: ' . $e->getMessage()
            ], 500);
        }
    }

    private function fetchPublishedCollectionsFromAPI($orgId)
    {
        try {
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'userkey' => $this->getUserKey(),
            ];

            $body = ['org' => $orgId];

            $response = Http::withHeaders($headers)
                ->asForm()              // API expects form data
                ->timeout(180)          // long timeout for large orgs
                ->post(env('API_BASE_URL') . '/list_collections_of_org/v1', $body);

            if ($response->successful()) {
                $json = $response->json();
                // Normalize payload: API may wrap data in "res"
                $data = (is_array($json) && array_key_exists('res', $json)) ? $json['res'] : $json;

                if (!is_array($data)) {
                    Log::warning('Unexpected API payload for list_collections_of_org', ['json' => $json]);
                    return [];
                }

                return $data;
            }

            throw new \Exception('API request failed with status: ' . $response->status());
        } catch (\Exception $e) {
            Log::error('Failed to fetch published collections from API: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Upsert collections by collection_id and delete ones not present in API response.
     */
    private function processOrganizationCollections(int $organizationId, array $collections): array
    {
        // If a wrapper slipped through, unwrap it
        if (isset($collections['res']) && is_array($collections['res'])) {
            $collections = $collections['res'];
        }

        $stats = [
            'collections_processed' => 0,
            'collections_created' => 0,
            'collections_updated' => 0,
            'collections_deleted' => 0,
        ];

        // incoming IDs from API
        $incoming = array_values(array_filter(array_map(fn ($c) => $c['collection_id'] ?? null, $collections)));
        $incomingSet = array_flip($incoming);

        // existing cloud collections for this org having external ids
        $existing = \App\Models\Collection::query()
            ->where('organization_id', $organizationId)
            ->whereNotNull('collection_id')
            ->get()
            ->keyBy('collection_id');

        // delete ones not returned
        $toDelete = $existing->keys()->filter(fn ($id) => !isset($incomingSet[$id]))->all();
        if (!empty($toDelete)) {
            $stats['collections_deleted'] = \App\Models\Collection::where('organization_id', $organizationId)
                ->whereIn('collection_id', $toDelete)
                ->delete();
        }

        $now = now();
        $toInsert = [];
        $toUpdate = [];

        foreach ($collections as $c) {
            $stats['collections_processed']++;
            $extId = $c['collection_id'] ?? null;
            if (!$extId) {
                continue;
            }

            $storageType = $this->determineStorageType($c);
            $createdAt = isset($c['collectionCreatedDate']) ? \Carbon\Carbon::parse($c['collectionCreatedDate']) : $now;

            if (isset($existing[$extId])) {
                // collect for update
                $toUpdate[] = [
                    'id' => $existing[$extId]->id,
                    'collection_name' => $c['collection_name'] ?? $existing[$extId]->collection_name,
                    'published_collection_name' => $c['collection_name'] ?? $existing[$extId]->published_collection_name,
                    'storage_type' => $storageType,
                    'is_synced' => 1,
                    'is_cloud_collection' => 1,
                    'updated_at' => $now,
                ];
                $stats['collections_updated']++;
            } else {
                // collect for insert
                $toInsert[] = [
                    'organization_id' => $organizationId,
                    'collection_id' => $extId,
                    'collection_name' => $c['collection_name'] ?? '',
                    'published_collection_name' => $c['collection_name'] ?? '',
                    'storage_type' => $storageType,
                    'is_synced' => 1,
                    'is_cloud_collection' => 1,
                    'created_at' => $createdAt,
                    'updated_at' => $now,
                ];
                $stats['collections_created']++;
            }
        }

        if (!empty($toInsert)) {
            \App\Models\Collection::insert($toInsert);
        }

        // apply updates
        foreach ($toUpdate as $row) {
            \App\Models\Collection::where('id', $row['id'])->update([
                'collection_name' => $row['collection_name'],
                'published_collection_name' => $row['published_collection_name'],
                'storage_type' => $row['storage_type'],
                'is_synced' => $row['is_synced'],
                'is_cloud_collection' => $row['is_cloud_collection'],
                'updated_at' => $row['updated_at'],
            ]);
        }

        return $stats;
    }

    private function determineStorageType($collectionData)
    {
        // Derive by first file hints
        if (!empty($collectionData['files']) && is_array($collectionData['files'])) {
            $f = $collectionData['files'][0] ?? [];
            if (array_key_exists('bucket', $f)) return 'S3';
            if (array_key_exists('sharepoint_site', $f)) return 'SharePoint';
            if (array_key_exists('vault', $f)) return 'MFiles';
        }
        // fallback by naming convention
        $name = (string)($collectionData['collection_name'] ?? '');
        if (str_ends_with(strtolower($name), '-s3')) return 'S3';
        if (str_ends_with(strtolower($name), '-sharepoint')) return 'SharePoint';
        if (str_ends_with(strtolower($name), '-mfiles')) return 'MFiles';
        return 'S3';
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

    /**
     * Sync files for each collection returned by the API.
     * - Upsert by composite key: (collection_id, file_name, bucket_sp_site_name)
     * - Delete files not returned by API for that collection
     */
    private function syncFilesForCollections(int $organizationId, array $apiCollections): array
    {
        // Build map of external collection_id => local DB id
        $extIds = array_values(array_filter(array_map(fn ($c) => $c['collection_id'] ?? null, $apiCollections)));
        if (empty($extIds)) {
            return [
                'files_processed' => 0,
                'files_created' => 0,
                'files_updated' => 0,
                'files_deleted' => 0,
            ];
        }

        $collectionIdMap = \App\Models\Collection::query()
            ->where('organization_id', $organizationId)
            ->whereIn('collection_id', $extIds)
            ->pluck('id', 'collection_id')
            ->toArray();

        $stats = [
            'files_processed' => 0,
            'files_created' => 0,
            'files_updated' => 0,
            'files_deleted' => 0,
        ];

        foreach ($apiCollections as $collection) {
            $extId = $collection['collection_id'] ?? null;
            if (!$extId || !isset($collectionIdMap[$extId])) {
                continue;
            }
            $localCollectionId = (int) $collectionIdMap[$extId];

            // Existing files keyed by composite key
            $existing = \App\Models\CollectionData::where('collection_id', $localCollectionId)
                ->get()
                ->keyBy(function ($row) {
                    return strtolower(($row->file_name ?? '') . '|' . ($row->bucket_sp_site_name ?? ''));
                });

            // Incoming files keyed the same way
            $incoming = [];
            foreach (($collection['files'] ?? []) as $item) {
                if (!isset($item['file']['name'])) {
                    continue;
                }
                $fileName = $item['file']['name'];
                $size = $item['file']['size'] ?? null;
                $lastModified = $item['file']['collectionUsedFileDate'] ?? null;
                $bucketSite = $this->getBucketSiteName($item);
                $key = strtolower($fileName . '|' . ($bucketSite ?? ''));

                $incoming[$key] = [
                    'file_name' => $fileName,
                    'size' => $size,
                    'bucket_sp_site_name' => $bucketSite,
                    'file_id' => $this->sanitizeFileName(($bucketSite ?? '') . '-' . $fileName),
                    'last_modified' => $lastModified,
                ];
                $stats['files_processed']++;
            }

            // Delete files not in API
            $toDeleteIds = [];
            foreach ($existing as $key => $row) {
                if (!isset($incoming[$key])) {
                    $toDeleteIds[] = $row->id;
                }
            }
            if (!empty($toDeleteIds)) {
                $deleted = \App\Models\CollectionData::whereIn('id', $toDeleteIds)->delete();
                $stats['files_deleted'] += $deleted;
            }

            // Prepare inserts and updates
            $toInsert = [];
            $toUpdate = [];

            foreach ($incoming as $key => $f) {
                if (isset($existing[$key])) {
                    $row = $existing[$key];

                    $update = [];
                    if ((int) $row->size !== (int) ($f['size'] ?? 0)) {
                        $update['size'] = $f['size'];
                    }
                    if (($row->last_modified ?? null) !== $f['last_modified']) {
                        $update['last_modified'] = $f['last_modified'];
                    }
                    if (!empty($update)) {
                        $update['updated_at'] = now();
                        \App\Models\CollectionData::where('id', $row->id)->update($update);
                        $stats['files_updated']++;
                    }
                } else {
                    $toInsert[] = [
                        'collection_id' => $localCollectionId,
                        'file_name' => $f['file_name'],
                        'size' => $f['size'],
                        'bucket_sp_site_name' => $f['bucket_sp_site_name'],
                        'file_id' => $f['file_id'],
                        'last_modified' => $f['last_modified'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $stats['files_created']++;
                }
            }

            if (!empty($toInsert)) {
                foreach (array_chunk($toInsert, 500) as $chunk) {
                    \App\Models\CollectionData::insert($chunk);
                }
            }
        }

        return $stats;
    }
}
