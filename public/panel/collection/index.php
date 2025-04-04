<?php

$csrfToken = csrf_token();

$protocol = isset($_SERVER['HTTPS']) &&
    $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$base_url = $protocol . $_SERVER['HTTP_HOST'] . '/';
?>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="<?php echo $base_url; ?>public/assets/css/collection.css">
<div id="csrf-token-container">
    <input type="hidden" id="csrf-token" value="<?= $csrfToken; ?>">
</div>
<div id="loader-wrapper">
    <div id="loader">
        <div class="loader-container">
            <b>Loading, please wait...</b><i class="fa fa-gear fa-spin" style="font-size:24px"></i>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <div class="row" id="collection-wrapper">
            <div class="col-md-6 col-sm-12 d-flex align-items-center">
                <div class="col-md-3"><label>Select Organization</label></div>
                <div class="col-md-9">
                    <select class="form-control" id="orgID">
                        <?php if ($orgID == 0): ?>
                            <option value="" data-feedback='1'>-Select-</option>
                            <?php
                            $orgs = \App\Organization::orderBy("organizationShortName", 'asc')->get();
                            if (!$orgs->isEmpty()) {
                                foreach ($orgs as $org) {
                            ?>
                                    <option value="<?= $org->organizationId; ?>" data-feedback='<?= $org->feedback; ?>'>
                                        <?= $org->organizationShortName; ?>
                                    </option>
                            <?php
                                }
                            }
                            ?>
                        <?php else: ?>
                            <?php
                            $org = \App\Organization::find($orgID);
                            ?>
                            <option value="<?= $org->organizationId; ?>" data-feedback='<?= $org->feedback; ?>'>
                                <?= $org->organizationShortName; ?>
                            </option>
                            <?php
                            ?>
                        <?php endif; ?>
                    </select>
                    <div class="organization-help-text">Select your organization and then select storage type to start your input</div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12 d-flex align-items-center">
                <div class="col-md-3"><label>Select Storage Type</label></div>
                <div class="col-md-9">
                    <select id="storage_type" disabled name="storage_type" class="form-control">
                        <option value="">Select Type</option>
                    </select>
                </div>
            </div>

        </div>
    </div>
    <script type="application/javascript">
        var apiURL = '<?= env('API_URL'); ?>/api/charts/';
    </script>
</div>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <div class="card" id="cloud-storage">
            <div class="card-header card-header-documents">
                <h2>Cloud Storage</h2>
                <div class="input-group">
                    <input type="text" class="form-control" id="search-cloud-storage" placeholder="Search">
                </div>
            </div>
            <div class="card-body collection-card-body">
                <div class="accordion-container">
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-12" id="documents-collection">
        <div class="card">
            <div class="card-header card-header-documents">
                <h2>Collections</h2>
                <div class="document-action">
                    <button class="btn btn-xs btn-primary" disabled id="new-collecion">Add</button>
                    <div class="input-group">
                        <input type="text" class="form-control" id="document-search" placeholder="Search">
                    </div>

                </div>
            </div>
            <div class="card-body collection-card-body">

            </div>
        </div>
    </div>
</div>

<div class="modal fade in" tabindex="-1" role="dialog" id="create-collection">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="border-radius:5px 0">
                <h2>New Collection</h2>
            </div>
            <div class="modal-body">
                <h4 class="rule-validation">Collection name guidance:</h4>
                <ul>
                    <li>Must not be blank.</li>
                    <li>Spaces are not allowed in the collection name.</li>
                    <li>Must be between 3 and 63 characters long.</li>
                    <li>Must start capital letter or a digit, and end a lowercase letter or a digit. The name can contain dots, hyphens and underscores.</li>
                    <li>Cannot contain consecutive special characters (e.g. '..' or '--' or '__').</li>
                    <li>Must not match the format of an IP address (e.g. `192.168.0.1` ).</li>
                    <li>Must not already exist.</li>
                </ul>
                <form id="form-collection" action="">
                    <input type="hidden" id="action-mode" value="create">
                    <input type="hidden" id="previous-name" data-collection-id="" value="">

                    <div class="form-group">
                        <label>Collection Name</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="collection-name" aria-describedby="colection-part2">
                            <span class="input-group-addon" id="colection-part2"></span>
                        </div>
                        <div class="help-text text-danger"></div>
                    </div>

                    <div class="form-group">
                        <label>Collection Notes</label>
                        <textarea class="form-control" id="collection-description"></textarea>
                        <div class="help-text text-danger"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="close-modal">Cancel</button>
                <button class="btn btn-primary" id="add-collection">Save</button>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" tabindex="-1" role="dialog" id="open-edit-collection-note">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="border-radius:5px 0">
                <h2>Edit Collection Note</h2>
            </div>
            <div class="modal-body">
                <form id="form-collection" action="">
                    <input type="hidden" id="collection-id" data-collection-id="" value="">
                    <div class="form-group">
                        <label>Collection Notes</label>
                        <textarea class="form-control" id="collection-note"></textarea>
                        <div class="help-text text-danger"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="close-note-modal">Cancel</button>
                <button class="btn btn-primary" id="edit-collection-note">Save</button>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo $base_url; ?>public/assets/js/manage-collection.js?v12" type="text/javascript"></script>
