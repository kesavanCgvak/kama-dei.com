var ajaxRequests = [];
$(function () {
    setCFRFToken();
    toggleAccordion();
    initContextMenu();
    setupToastr();
    initChangevents();
    initClickEvents();
    getSystemSourceTypes();
    $('#form-collection').on('keydown', function (event) {
        // Check if the Enter key (keyCode 13) is pressed
        if (event.keyCode === 13) {
            event.preventDefault(); // Prevent form submission
        }
    });
});

function getSystemSourceTypes() {
    $.ajax({
        type: "GET",
        url: "/get-system-source-types",
        dataType: 'json',
        beforeSend: function () {
            showLoader();
        },
        success: function (response) {
            hideLoader();
            if (response.state === 'success') {
                // Get the select element
                let $storageTypeSelect = $('#storage_type');

                // Clear existing options except the first one
                $storageTypeSelect.find('option:not(:first)').remove();

                // Add new options from the response
                response.data.forEach(function(item) {
                    $storageTypeSelect.append(
                        $('<option>', {
                            value: item.value,
                            text: item.value + ' (' + item.provider + ')'
                        })
                    );
                });

                hideLoader();
            } else {
                throw new Error(response.message || "Unknown error occurred");
            }
        },
        error: function (xhr, status, error) {
            hideLoader();
            console.error('AJAX Error:', status, error);
            toastr.error(xhr.responseJSON?.message || "Error loading storage types.");
        },
        complete: function () {
            hideLoader();
        }
    });
}

// Track AJAX requests globally
$(document).ajaxSend(function (event, jqXHR) {
    ajaxRequests.push(jqXHR);
});

// Remove completed or aborted requests from the list
$(document).ajaxComplete(function (event, jqXHR) {
    ajaxRequests = ajaxRequests.filter((req) => req !== jqXHR);
});

// Function to cancel all ongoing AJAX requests
function cancelAllAjaxRequests() {
    ajaxRequests.forEach((request) => {
        if (request && request.readyState !== 4) {
            request.abort();
        }
    });
    ajaxRequests = []; // Clear the array after aborting requests
}

function setCFRFToken() {
    let csrfToken = $('#csrf-token').val();
    // Set up global AJAX configuration
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    });
}

function closeModel($selector) {
    $selector.removeClass('show');
}



function initClickEvents() {
    $(document).on("click", ".folder-name", function (event) {
        event.preventDefault();
        let folderId = $(this).data('folder-name');
        toggleFolder(folderId, event)
    });

    $(document).on("click", "#new-collecion", function (event) {
        event.preventDefault();
        openModal('', '', '', 'create');
    });
    $(document).on("click", "#add-collection", function (event) {
        event.preventDefault();
        addNewCollection();
    });

    $(document).on("click", "#edit-collection-note", function (event) {
        event.preventDefault();
        updateCollectionNote();
    });

    $(document).on("click", "#close-modal", function (event) {
        event.preventDefault();
        closeModel($('#create-collection'));
    });
    $(document).on("click", "#close-note-modal", function (event) {
        event.preventDefault();
        closeModel($('#open-edit-collection-note'));
    });

    $(document).on("click", "#close-note-modal", function (event) {
        event.preventDefault();
        closeModel($('#open-edit-collection-note'));
    });


    $(document).on("click", ".refresh-collection", function (event) {
        event.preventDefault();
        getClouldBucketItems($(this).closest('.accordion-item'), $(this).closest('.accordion-item').attr('data-bucket-name'));
    });

}

$(document).on("keyup", "#search-cloud-storage", function (event) {
    event.preventDefault();
    let searchValue = $(this).val();
    if (searchValue.trim().length < 3) {
        clearSearch('cloud');
    } else {
        expandAllAccordions($('#cloud-storage'));
        initDocumentSearch('cloud');
    }
});

$(document).on("keyup", "#document-search", function (event) {
    event.preventDefault();
    let searchValue = $(this).val();
    if (searchValue.trim().length < 3) {
        clearSearch('local');
    } else {
        expandAllAccordions($('#documents-collection'));
        initDocumentSearch('local');
    }
});

$(document).on('change', '#orgID', function (e) {
    $("#storage_type").val('');
    $("#cloud-storage").find('.collection-card-body .accordion-container').html('');
    $("#documents-collection").find('.collection-card-body').html('');
    cancelAllAjaxRequests();
});

$(document).on("click", ".file-delete", function () {
    let $selectedItem = $(this).closest('li');
    let $accordionItem = $selectedItem.closest('.accordion-item');
    let collectionFileId = $(this).closest('li').attr('data-details-id');
    let elementIdentifier = $(this).closest('li').attr('data-file-id');
    let confirmation = confirm('Are you sure you want to delete this file?');
    if (!confirmation) {
        return false;
    }

    let data = { id: collectionFileId };
    $selectedItem.addClass('delete-item');
    $.ajax({
        type: "POST",
        url: "/deleteLocalFile",
        data: data,
        dataType: 'json',
        beforeSend: function () {
            showLoader();
        },
        success: function (response) {
            if (response.status === 'success') {
                hideLoader();
                toastr.success(response.message || "File deleted successfully.");
              //  $(`#cloud-storage .accordion-content .file-item[data-file-id="${elementIdentifier}"]`).removeClass('file-selected').find('.list-action').html('');
              //  $element = $(`#documents-collection .accordion-content .file-item[data-file-id="${elementIdentifier}"]`);
              $selectedItem.remove();
                updateUnPublishedStatus($accordionItem);
                setTimeout(function () {
                    initSortable();
                    getFileDifferences(true);
                    //updateFileNotexist(); // Update file existence status
                }, 200);

            } else {
                throw new Error(response.message || "Unknown error occurred");
            }
        },
        error: function (xhr, status, error) {
            hideLoader();
            console.error('AJAX Error:', status, error);
            toastr.error(xhr.responseJSON?.message || "Error on deleting file.");
        },
        complete: function () {
            hideLoader();
        }
    });

});

$(document).on("click", ".delete-collection", function (event) {
    event.preventDefault();
    const originalItem = $(this).closest('.accordion-item');
    const submenu = $(this).closest('.submenu');
    submenu.slideUp(200);
    let collection_name = originalItem.attr('data-collection-name');
    let is_cloud_collection = originalItem.attr('data-is-cloud-collecion');
    let org_id = $('#orgID').val();
    let storage_type = $('#storage_type').val();
    let collection_id = originalItem.attr('data-id');
    let consfirmation = confirm('Are you sure you want to delete this collection?');
    if (!consfirmation) {
        return false;
    }
    if (is_cloud_collection == 1) {
        deleteCloudCollection(collection_name, org_id, storage_type, collection_id);
    }
    originalItem.remove();
    deleteLocalCollection(collection_id);
});


function deleteCloudCollection(collection_name, org_id, storage_type, collection_id) {
    $.ajax({
        type: "POST",
        url: "/deleteCollection",
        data: {
            collection_name: collection_name,
            org_id: org_id,
            storage_type: storage_type
        },
        dataType: 'json',
        beforeSend: function () {
            showLoader();
        },
        success: function (response) {
            if (response.status === 'success') {
                hideLoader();
                toastr.success(response.message || "Collection deleted successfully.");

            } else {
                throw new Error(response.message || "Unknown error occurred");
            }
        },
        error: function (xhr, status, error) {
            hideLoader();
            console.error('AJAX Error:', status, error);
            toastr.error(xhr.responseJSON?.message || "Error on deleting collection.");
        },
        complete: function () {
            hideLoader();
        }
    });
};


function deleteLocalCollection(collection_id) {
    $.ajax({
        type: "POST",
        url: "/deleteLocalCollection",
        data: { collection_id },
        dataType: 'json',
        beforeSend: function () {
            showLoader();
        },
        success: function (response) {
            if (response.status == 'success') {
                hideLoader();
                toastr.success(response.message || "Collection deleted successfully.");
                getFileDifferences(true);
            }
        },
        error: function (xhr, status, error) {
            hideLoader();
            console.error('AJAX Error:', status, error);
            toastr.error(xhr.responseJSON?.message || "Error on deleting collection.");
        },
        complete: function () {
            hideLoader();
        }
    });
}

function bucketNameExists(name) {
    return $(`.accordion-item[data-collection-name="${name}"]`).length > 0;
}


function updateCollectiononDb(collection, id) {
    $.ajax({
        url: '/collections/' + id,
        method: 'PUT',
        data: JSON.stringify(collection),
        contentType: 'application/json',
        beforeSend: function () {
            showLoader();
        },
        success: function (response) {
            // Handle success
            // toastr.success('Collection updated successfully');
        },
        error: function (xhr, status, error) {
            hideLoader();
            // Handle error
            toastr.error('Error updating collection');
        }
    });
}

$(document).on("click", ".edit-collection-note", function (event) {
    event.preventDefault();
    const originalItem = $(this).closest('.accordion-item');
    const submenu = $(this).closest('.submenu');
    submenu.slideUp(200);
    let baseBucketName = originalItem.attr('data-collection-name');
    let collectionDescription = originalItem.attr('data-collection-description');
    let id = originalItem.attr('data-id');
    openNoteModal(collectionDescription, id);
});

$(document).on("click", ".rename-collection", function (event) {
    event.preventDefault();
    const originalItem = $(this).closest('.accordion-item');
    const submenu = $(this).closest('.submenu');
    submenu.slideUp(200);
    let baseBucketName = originalItem.attr('data-collection-name');
    let collectionDescription = originalItem.attr('data-collection-description');
    let storageType = $('#storage_type').val();
    baseBucketName = baseBucketName.replace(`-${storageType}`, "");
    let id = originalItem.attr('data-id');
    openModal(baseBucketName, collectionDescription, id, "update");
});

function openNoteModal(collectionDescription, id) {
    const modal = $('#open-edit-collection-note');
    modal.find('.help-text').text('');
    modal.find('#collection-note').val(collectionDescription);
    modal.find('#collection-id').val(id);
    modal.addClass('show');
}

function openModal(name = "", description = "", id = "", mode = 'create') {
    const modal = $('#create-collection');
    modal.find('#action-mode').val(mode);
    modal.find('.help-text').text('');
    modal.find('#collection-name').val(name);
    modal.find('#collection-description').val(description);
    modal.find('#previous-name').val(name);
    modal.find('#previous-name').attr('data-collection-id', id);
    $("#colection-part2").text(`-${$("#storage_type").val()}`);
    if (mode === 'create') {
        modal.find('.modal-header h2').text('New Collection');
        modal.find('.modal-footer #add-collection').text('Save');
        modal.addClass('show');
        return false;
    }
    modal.find('.modal-header h2').text('Rename Collection');
    modal.find('.modal-footer #add-collection').text('Rename');
    modal.addClass('show');
}



$(document).on("click", ".copy-collection", function (event) {
    event.preventDefault();
    const originalItem = $(this).closest('.accordion-item');
    const submenu = $(this).closest('.submenu');
    submenu.slideUp();
    const clonedItem = originalItem.clone();
    let baseBucketName = clonedItem.attr('data-collection-name');
    let newBucketName = baseBucketName + '-copy1';

    // Add a numeric suffix if the bucket name already exists
    let counter = 1;
    while (bucketNameExists(newBucketName)) {
        counter++;
        newBucketName = `${baseBucketName}-copy${counter}`;
    }

    // Update the `data-bucket-name` attribute in the cloned item
    clonedItem.attr('data-collection-name', newBucketName);
    clonedItem.find('.accordion-header').attr('data-collection', newBucketName);
    // Update the title with the same suffix for display
    clonedItem.find('.accordion-title').text(newBucketName);
    clonedItem.attr('id', "section-" + newBucketName);
    clonedItem.attr('data-published-collection-name', baseBucketName);

    updateUnPublishedStatus(clonedItem.closest('.accordion-item'));
    copyCollectionsToDatabases(clonedItem);
    initSortable();
});

function getCopyNumber(str) {
    let match = str.match(/-copy-(\d+)$/);
    return match ? parseInt(match[1], 10) : null; // Extracts and converts to integer
}

function copyCollectionsToDatabases(clonedItem) {
    showLoader();
    let organization_id = $('#orgID').val();
    let storage_type = $('#storage_type').val();
    let collection_name = clonedItem.attr("data-collection-name");
    let collection_description = clonedItem.attr("data-collection-description");

    let collection_data = [];
    $.each($(clonedItem).find('.file-item'), function (index, file) {
        let $file = $(file);
        collection_data.push({
            file_name: $file.attr('data-file-name'),
            size: $file.attr('data-file-size'),
            bucket_sp_site_name: $file.attr('data-bucket-name'),
            file_id: $file.attr('data-file-id'),
            last_modified: $file.attr('data-last-modified')
        })
    });
    let data = {
        organization_id,
        storage_type,
        collection_name,
        collection_data,
        collection_description
    };
    $.ajax({
        type: "post",
        url: "/copy-collection",
        data: data,
        beforeSend: function () {
            showLoader();
        },
        success: function (response) {
            hideLoader();
            if (response.status === 'error') {
                toastr.error('Error on updating file.');
                return;
            }
            const collecion = response.collection;
            clonedItem.attr('data-id', collecion.id)
                .attr('data-is-cloud-collecion', 0)
                .attr('data-published-collection-name', '')
                .attr('id', 'section-' + collecion.id);
            clonedItem.find('.accordion-header').attr('data-id', collecion.id);

            $.each(collecion.collection_data, function (index, collectionItem) {
                clonedItem.find(`.accordion-content .file-item[data-file-id="${collectionItem.file_id}"]`).attr('data-details-id', collectionItem.id);
            });
            clonedItem.find('.submenu').slideUp();
            $("#documents-collection").find(".collection-card-body").append(clonedItem);
            // Re-initialize the sortable
            initSortable();
            toastr.success('The file information copied successfully');
        },
        error: function (xhr, status, error) {
            hideLoader();
            console.error('AJAX error:', error);
            toastr.error('Failed to update file information.');
        },
    });
}


function initSortable() {
    const $sortableList = $("#documents-collection .accordion-content ul");
    // Initialize the sortable
    $sortableList.sortable({
        placeholder: "ui-state-highlight", // Highlight area where the item will be placed
        //  connectWith: ".sortable-connected", // Allow sorting with other lists (use the same class for other uls)
        cancel: '.local-files',
connectWith: ".ui-sortable", // Allow sorting with other lists
        connectToSortable: "#cloud-storage .accordion-content li",
        update: function (event, ui) {
            let droppedItem = ui.item; // The dropped item
            // Highlight the border of the dropped item
            droppedItem.css({
                border: "2px solid #007bff", // Blue border
                transition: "border 0.3s ease" // Smooth transition
            });
            // Optionally remove the highlight after a delay
            setTimeout(() => {
                droppedItem.css({
                    border: "",
                    transition: ""
                });
            }, 2000); // Remove after 2 seconds
            let data = {
                file_name: droppedItem.data('file-name'),
                size: droppedItem.data("file-size"),
                last_modified: droppedItem.data("last-modified"),
                collection_id: $(this).closest(".accordion-item").find(".accordion-header").data("id"),
                file_id: droppedItem.data("file-id"),
                bucket_name: droppedItem.data("bucket-name"),
            };

            let sortedItems = $(this).children("li");
            let seenIds = new Set();
            let hasDuplicates = false; // Flag to track duplicates

            // Remove duplicate <li> elements based on data-id
            sortedItems.each(function () {
                const dataId = $(this).data("file-id"); // Fetching data-id instead of id
                if (seenIds.has(dataId)) {
                    // Remove duplicate <li> with the same data-id
                    hasDuplicates = true; // Duplicate found
                    droppedItem.remove();
                } else {
                    seenIds.add(dataId);
                }
            });

            if (!hasDuplicates) {
                createLocalItems(data, droppedItem);
                return;

            } else {
                toastr.warning("Duplicate Item");
            }
            updateDropZoneMessage($(this)); // Ensure message updates when sorting
        },
        over: function (event, ui) {
            $(this).addClass("highlight-dropzone"); // Highlight the drop area when dragging over
        },
        out: function (event, ui) {
            $(this).removeClass("highlight-dropzone"); // Remove highlight when dragging out
        },
        receive: function (event, ui) {
            $(this).removeClass("highlight-dropzone"); // Remove highlight after drop
            updateDropZoneMessage($(this)); // Update message visibility
        }
    }).disableSelection();

     // Initialize message visibility
     $(".ui-sortable").each(function () {
        updateDropZoneMessage($(this));
    });
}

function createLocalItems(data, droppedItem) {
    data['_token'] = $('meta[name="csrf-token"]').attr('content');
    droppedItem.addClass('file-selected');
    $.ajax({
        type: "post",
        url: "/storeLocalIems",
        data: data,
        beforeSend: function () {
            showLoader();
        },
        success: function (response) {
            hideLoader();
            if (response.status === 'success') {
                //let droppedItem.find(`[data-file-id='${response.data.file_id}']`); // Find the element by data-file-id
                droppedItem.attr("data-details-id", response.data.id)
                droppedItem.find('.list-action').html(`<i class="fa fa-trash-o file-delete cursor-pointer"></i>`);
                droppedItem.addClass('local-files');
                updateUnPublishedStatus(droppedItem.closest('.accordion-item'));
                toastr.success('File information stored successfully.');
                setTimeout(function () { getFileDifferences(); }, 500); // Call getFileDifferences();
            }
        },
        error: function (xhr, status, error) {
            hideLoader();
            console.error('AJAX error:', error);
        }
    });
}


function updateUnPublishedStatus($element) {
    $element.addClass('not-published');
    $element.find('.accordion-header').attr('title', 'Not Published');
    $element.find('.submenu').find('.copy-collection').addClass('btn-disabled').prop('disabled', true);
}


$(document).off('click', ".sync-file").on('click', ".sync-file", function () {
    let $cloudFile = $(this).closest('li');
    let cloudFileId = $cloudFile.attr('data-file-id');
    let last_modified = $cloudFile.attr('data-last-modified');
    let $localFiles = $('#documents-collection .accordion-content .file-item[data-file-id="' + cloudFileId + '"]');
    // Extract IDs and other necessary data
    let localFileIds = [];
    let data = [];
    $localFiles.each(function () {
        localFileIds.push($(this).data('id'));
        data.push({
            id: $(this).data('details-id'),
            last_modified
        });
    });

    if (localFileIds.length === 0) {
        toastr.warning('No matching local files found to update.');
        return;
    }

    // Send the request with both IDs and the file data
    $.ajax({
        type: "POST",
        url: "/updateLocalFiles",
        data: JSON.stringify({ data }),  // Send both ids and files as payload
        contentType: 'application/json',  // Ensure the correct content type
        beforeSend: function () {
            showLoader();
        },
        success: function (response) {
            hideLoader();
            if (response.status === 'error') {
                toastr.error('Error on updating file.');
                return;
            }
            toastr.success('The file information has been synced successfully');
            $localFiles.each(function () {
                $(this).attr('data-last-modified', last_modified);
                $(this).find('.file-date').html(last_modified);
            });
            $localFiles.find('.file-date').text($cloudFile.find('.file-date').text());
            $localFiles.removeClass('file-outdated');
            $cloudFile.find('.list-action').html("");
            setTimeout(function () {
                getFileDifferences();
            }, 100);

        },
        error: function (xhr, status, error) {
            hideLoader();
            console.error('AJAX error:', error);
            toastr.error('Failed to update file information.');
        },
    });
});


function getFileDifferences(clearFileSelection = false) {
    // Loop through each file item in #accordionCloud
    let $cloudFiles = $('#cloud-storage .accordion-content .file-item');
    if (clearFileSelection) {
        $cloudFiles.removeClass('file-outdated file-selected');
    }
    $cloudFiles.each(function () {
        let cloudFileId = $(this).data('file-id');
        // Check for matching file in #accordionLocal
        let localFile = $('#documents-collection .accordion-content .file-item[data-file-id="' + cloudFileId + '"]');

        if (localFile.length > 0) {
            let cloudFile = $('#cloud-storage .accordion-content .file-item[data-file-id="' + cloudFileId + '"]');
            let localFileDate = localFile.attr("data-last-modified");
            let cloudFileDate = $(this).attr("data-last-modified");

            // Compare the file dates
            if (cloudFileDate != localFileDate) {
                cloudFile.attr('title', 'This file seems outdated in local collection. Please sync by clicking the sync icon.');
                cloudFile.find('.list-action').html(`<i class="fa fa-refresh cursor-pointer sync-file"></i>`);
                localFile.removeClass('file-selected').addClass('file-outdated');
                cloudFile.removeClass('file-selected').addClass('file-outdated');
                localFile.closest('.accordion-item').addClass('not-published');
                localFile.closest('.accordion-header').attr("title", "Not Published");
                let parentUl = cloudFile.closest('ul');
                if (parentUl.length > 0) {
                    let idString = parentUl.attr('id');
                    let trimmedId = idString.trim();
                    let id = trimmedId.replace('folder-', '');
                    if ($('[data-folder-name="' + id + '"]').find(`#icon-${id}`).hasClass('fa-folder-o')) {
                        $('[data-folder-name="' + id + '"]').trigger('click');
                    }
                }
            } else {
                cloudFile.removeClass('file-outdated').addClass('file-selected');
            }
        }
    });

    if ($cloudFiles.length > 0) {
        updateFileNotexist();
    }
}


function updateFileNotexist() {
    // Loop through each file item in #accordionLocal
    $('#documents-collection .accordion-content .file-item').each(function () {
        let localFileId = $(this).data('file-id');
        // Check for matching file in #accordionCloud
        let localFile = $('#documents-collection .accordion-content .file-item[data-file-id="' + localFileId + '"]');
        let cloudFile = $('#cloud-storage .accordion-content .file-item[data-file-id="' + localFileId + '"]');
        if (cloudFile.length > 0) {
            localFile.removeClass('file-not-exists');
        } else {
            localFile.addClass('file-not-exists').removeClass('file-selected file-outdated');
            localFile.closest('.accordion-item').addClass('not-published').find('.accordion-header').attr('title', 'Not Published');
            expandAllAccordions(localFile.closest('.accordion-item'));
        }
    });
}

function updateCollectionNote() {
    if (!validateCollectionNote()) {
        return false;
    }
    let collectionDescription = $('#collection-note').val().trim();
    let collecionItemId = $('#collection-id').val();
    let data = { 'collection_description': collectionDescription, 'collection_id': collecionItemId };
    $.ajax({
        type: "post",
        url: "/update-collection-note",
        data,
        dataType: 'json',
        beforeSend: function () {
            showLoader();
        },
        success: function (response) {
            hideLoader();
            if (response.status === 'success') {
                toastr.success("Collection renamed successfully.");
                $("#documents-collection").find(`#section-${collecionItemId}`).attr('data-collection-description', collectionDescription)
                    .find('h2.accordion-title').attr('title', collectionDescription);
            }
        },
        error: function (xhr, status, error) {
            hideLoader();
            console.error('AJAX Error:', status, error);
            toastr.error("Error on creating new collection.");
        },
        complete: function () {
            hideLoader();
            closeModel($('#open-edit-collection-note'));
        }
    });

}

function validateCollectionNote() {
    let $collectionNote = $('#collection-note');
    let description = $collectionNote.val().trim();
    let collectionId = $('#previous-name').attr('data-collection-id');
    const descHelpText = $collectionNote.siblings('.help-text'); // Help text element
    descHelpText.text('');
    if (description.length === 0) {
        descHelpText.text('Collection note cannot be empty.');
        return false

    }
    // Rule 9: Description must be fewer than 1,000 characters
    if (description.length > 1000) {
        descHelpText.text('Collection note should be fewer than 1,000 characters.');
        return false;
    }
    return true;
}

// function saveLocalCollection(overwrite=false) {
//     let actionMode = $("#action-mode").val();
//     let storage_type = $('#storage_type').val().trim();
//     let collectionName = $('#collection-name').val().trim() + `-${storage_type}`;
//     let collectionDescription = $('#collection-description').val().trim();
//     let collecionItemId = $('#previous-name').attr("data-collection-id");

//     closeModel($('#create-collection'));
//     if (actionMode === "create") {
//         saveLocalCollection(collectionName, 'collection')
//         return;
//     }
//     renameCollection(collectionName, collecionItemId, collectionDescription);
// }

function addNewCollection() {
    if (!validateCollectionName()) {
        return false;
    }
    let storage_type = $('#storage_type').val().trim();
    let collectionName = $('#collection-name').val().trim() + `-${storage_type}`;
    let collectionId = $('#previous-name').attr('data-collection-id');

    if ($('#action-mode').val() === 'create') {
        checkDuplicateCollection(collectionName, storage_type, collectionId, saveLocalCollection);
        return
    }
    checkDuplicateCollection(collectionName, storage_type, collectionId, renameCollection);
}


function renameCollection() {
    let collecionItemId = $('#previous-name').attr('data-collection-id');
    let storage_type = $('#storage_type').val();
    let collectionName = $('#collection-name').val().trim() + `-${storage_type}`;
    let collectionDescription = $('#collection-description').val().trim();
    let data = {
        "collection_name": collectionName,
        "collection_id": collecionItemId,
        "collection_description": collectionDescription
    };
    $.ajax({
        type: "post",
        url: "/rename-db-collection",
        data,
        dataType: 'json',
        beforeSend: function () {
            showLoader();
        },
        success: function (response) {
            hideLoader();
            if (response.status === 'success') {
                toastr.success("Collection renamed successfully.");
                $("#documents-collection").find(`#section-${collecionItemId}`)
                    .attr('data-collection-name', collectionName).attr('data-collection-description', collectionDescription);
                $("#documents-collection")
                    .find(`#section-${collecionItemId}`)
                    .addClass('not-published')
                    .find('.accordion-title')
                    .text(collectionName);
                $("#documents-collection")
                    .find(`#section-${collecionItemId}`)
                    .find("accordion-header")
                    .attr('title', 'Not Published');
                $("#documents-collection")
                    .find(`#section-${collecionItemId}`).addClass('not-published');
                $("#documents-collection")
                    .find(`#section-${collecionItemId}`).find('.submenu .copy-collection').attr("disabled", "disabled").addClass('btn-disabled');
                initSortable();
                closeModel($('#create-collection'))
            }
        },
        error: function (xhr, status, error) {
            hideLoader();
            console.error('AJAX Error:', status, error);
            toastr.error("Error on creating new collection.");
        },
        complete: function () {
            hideLoader();
        }
    });
}

function saveLocalCollection() {
    if (!validateCollectionName()) {
        return false;
    }
    let organization_id = $("#orgID").val();

    let storage_type = $('#storage_type').val();
    let collection_name = $('#collection-name').val().trim() + `-${storage_type}`;
    let collection_description = $('#collection-description').val().trim();
    let data = {
        organization_id,
        collection_name,
        collection_description,
        storage_type
    }
    $.ajax({
        type: "post",
        url: "/collections",
        data,
        dataType: 'json',
        beforeSend: function () {
            showLoader();
        },
        success: function (response) {
            hideLoader();
            if (response.status === 'error') {
                toastr.error(response.message || "Error on creating new collection.");
                return;
            }
            toastr.success("New collection created successfully.");
            closeModel($('#create-collection'))
            setTimeout(function () {
                getLocalCollections(storage_type, organization_id);
            }, 500);
        },
        error: function (xhr, status, error) {
            hideLoader();
            console.error('AJAX Error:', status, error);
            toastr.error("Error on creating new collection.");
        },
        complete: function () {
            hideLoader();
            closeModel($('#create-collection'))
        }
    });
}

function validateCollectionName() {
    let $collection = $('#collection-name'); // Collection name input element
    let $collectionDescription = $('#collection-description');  // Collection description input element
    let description = $collectionDescription.val().trim();   // Trim whitespace from the input
    const storageType = $("#storage_type").val().trim();   // Get and trim the storage type value dynamically
    let collectionName = $collection.val().trim();   // Trim whitespace from the collectionName
    const helpText = $collection.closest('.input-group').siblings('.help-text');  // Help text element
    const descHelpText = $collectionDescription.siblings('.help-text');  // Help text element

    // Rule 1: Cannot be empty
    if (collectionName === "") {
        helpText.text('Collection name cannot be empty.');
        return false;
    }

    // Rule 2: Length must be between 3 and 63 characters
    if (collectionName.length < 3 || collectionName.length > 63) {
        helpText.text('Collection name must be between 3 and 63 characters.');
        return false;
    }

    // Rule 3: Must start with a capital letter or a digit and end with a lowercase letter or a digit
    if (!/^[A-Z0-9].*[a-z0-9]$/.test(collectionName)) {
        helpText.text('Collection name must start with a capital letter or a digit and end with a lowercase letter or a digit.');
        return false;
    }

    // Rule 4: Only allow dots, hyphens, and underscores as special characters, but not consecutively
    if (/[^A-Za-z0-9._-]/.test(collectionName)) {
        helpText.text('Collection name can only contain letters, numbers, dots (.), hyphens (-), and underscores (_).');
        return false;
    }
    if (/[-._]{2,}/.test(collectionName)) {
        helpText.text('Collection name cannot contain consecutive special characters (.. , -- , __ ).');
        return false;
    }
    if (/[-._]$/.test(collectionName) || /^[._-]/.test(collectionName)) {
        helpText.text('Collection name cannot start or end with a special character.');
        return false;
    }

    // Rule 5: Must not be a valid IP address
    const ipRegex =
        /^(?:(?:25[0-5]|2[0-4][0-9]|[0-1]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[0-1]?[0-9][0-9]?)$/;
    if (ipRegex.test(collectionName)) {
        helpText.text('Collection name cannot be an IP address.');
        return false;
    }

    // Rule 6: Must not already exist (custom validation)
    if (bucketNameExists(collectionName)) {
        helpText.text('Collection name already exists.');
        return false;
    }

    // Rule 7: Must be different when renaming
    if ($('#action-mode').val() === 'update' && $("#previous-name").val().trim() === collectionName) {
        helpText.text('Provide a new name for renaming.');
        return false;
    }

    // Rule 8: Description must be fewer than 1,000 characters
    if (description.length > 1000) {
        descHelpText.text('The description must be fewer than 1,000 characters.');
        return false;
    }

    // If all rules pass, clear help text and return true
    helpText.text('');
    return true;
}

function checkDuplicateCollection(collection_name, storage_type, collection_id, callback) {
    $.ajax({
        type: "post",
        url: "/check-collection",
        data: { collection_name, storage_type, collection_id },
        dataType: 'json',
        beforeSend: function () {
            showLoader();
        },
        success: function (response) {
            hideLoader();
            let data = response.data;
            console.log(data);
            if (data !== null && collection_id !== null) {
                let consfirmation = confirm('A Collection already exists with this name. If you proceed, the Collection will be overwritten and document assignments will be removed.');
                if (consfirmation) {
                    callback(true);
                }
                return;
            }
            if (data !== null && collection_id === null) {
                toastr.error('Collection already exists with this name.');
                return;
            }
            if(data === null) {
                callback(false);
            }


        },
        error: function (xhr, status, error) {
            hideLoader();
            console.error('AJAX Error:', status, error);
            toastr.error("Error on creating new collection.");
        },
        complete: function () {
            hideLoader();
        }
    });
}

function clearSearch(type) {
    $parentWrapper = $('#cloud-storage');
    if (type === 'local') {
        $parentWrapper = $('#documents-collection');
    }
    $parentWrapper.find('.folder-name').removeClass('hide-search');
    $parentWrapper.find('.file-item').removeClass('hide-search');
}

function initDocumentSearch(type) {
    $searchContainer = $('#search-cloud-storage');
    $parentWrapper = $('#cloud-storage');
    if (type === 'local') {
        $searchContainer = $('#document-search');
        $parentWrapper = $('#documents-collection');
    }
    let searchTerm = $searchContainer.val().trim().toLowerCase();
    let $accordionItems = $parentWrapper.find('.accordion-item');

    if ($accordionItems.length === 0) {
        return;
    }
    if (searchTerm === '') {
        $parentWrapper.find('.folder-name').removeClass('hide-search');
        $parentWrapper.find('.file-item').removeClass('hide-search');
        return;
    }

    setTimeout(function () {

        $parentWrapper.find('.file-item').each(function () {
            const title = $(this).find('.title').text().toLowerCase();

            $parentWrapper.find('.folder-name').addClass('hide-search');
            if (title.includes(searchTerm.toLowerCase())) {
                $(this).removeClass('hide-search');
            } else {
                $(this).addClass('hide-search');
            }
        });
    }, 500);
}

function toggleFolder(folderId, event) {
    const fileList = $('#folder-' + folderId);
    const icon = document.getElementById('icon-' + folderId);
    if (fileList.css('display') === 'none') {
        fileList.stop(true, true).slideDown(400);
        $('#icon-' + folderId).removeClass('fa-folder-o').addClass('fa-folder-open-o');
    } else {
        fileList.stop(true, true).slideUp(400);
        $('#icon-' + folderId).removeClass('fa-folder-open-o').addClass('fa-folder-o');
    }
    event.stopPropagation();
    return false;
}

function initChangevents() {
    let $newCollection = $("#new-collecion");
    $(document).on("change", "#storage_type", function () {
        cancelAllAjaxRequests();
        let storage_type = $(this).val();
        let orgID = $("#orgID").val();
        $("#cloud-storage").find('.collection-card-body .accordion-container').html('');
        $("#documents-collection").find('.collection-card-body').html('');

        if (orgID == '') {
            toastr.warning("Select Organization.");
            return;
        }
        if (storage_type === '') {
            $newCollection.attr("disabled", "disabled");
            return;
        }
        $newCollection.removeAttr("disabled");

        // Wait for getClouldCollection to complete before calling getLocalCollections
        getClouldCollection(storage_type, orgID).then(function () {
            getLocalCollections(storage_type, orgID);
        }).catch(function () {
            toastr.error("An error occurred while fetching cloud collections.");
        });
    });
}

function getLocalCollections(storage_type, orgID) {

    try {
        $.ajax({
            type: "post",
            url: "/getLocalCollections",
            data: {
                orgID: orgID,
                storage_type: storage_type
            },
            beforeSend: function () {
                showLoader();
            },
            success: function (response) {
                hideLoader();
                if (response.status === 'success') {
                    let collections = response.data;
                    $("#documents-collection").find('.collection-card-body').html('');
                    $.each(collections, function (index, collection) {
                        $("#documents-collection").find('.collection-card-body').append(createLocalAccordionItem(collection));
                    });
                }
                $('#local_bucket_section').html(response);
                initSortable();
                getFileDifferences();
            },
            error: function (xhr, status, error) {
                hideLoader();
                console.error('AJAX error:', error);
            }
        })

    } catch (error) {
        console.error(`Error initializing getLocaCollections: ${error}`);
    }
}

function buildLocalCollectionAccordion(collections) {
    let $accordion = $("#documents-collection").find(".collection-card-body")
    $.each(collections, function (index, collection) {
        $accordion.appendTo(createCloudAccordionItem(collection, "collection", collection.is_synced));
    });
}

function setupToastr() {
    toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }
}

function showLoader() {
    $('#loader-wrapper').show();
}

function hideLoader() {
    $('#loader-wrapper').hide();
}

function getClouldCollection(storage_type, org_id) {
    $("#accordionLocal").html('');
    let data = {
        storage_type,
        org_id
    };

    // Create a deferred object for the entire operation
    let deferred = $.Deferred();

    $.ajax({
        type: "post",
        url: "/getcollections",
        data,
        dataType: 'HTML',
        beforeSend: function () {
            showLoader();
        },
        success: function (response) {
            try {
                let responseData = JSON.parse(response);
                if (responseData.state == 'success') {
                    // Use setAccordions with promise chaining
                    setAccordions(responseData.data)
                        .then(() => {
                            initSortable();
                            getFileDifferences();
                            // Resolve the deferred object when everything is done
                            deferred.resolve();
                        })
                        .catch((error) => {
                            console.error("Error in setAccordions:", error);
                            deferred.reject();
                        });
                } else if (responseData.state == 'error') {
                    toastr.error(responseData.message);
                    deferred.reject();
                } else {
                    deferred.reject(); // Reject if the response state is not success
                }
            } catch (e) {
                conasole.error(e.message);
                toastr.error("Failed to process response data.");
                deferred.reject();
            }
        },
        error: function (xhr, status, error, message) {
            hideLoader();
            $("#cloud_bucket_section").html('');
            toastr.error("No data received from API.");
            deferred.reject();
        },
        complete: function () {
            hideLoader();
        }
    });

    // Return the deferred object as a promise
    return deferred.promise();
}

function initChangevents() {
    let $newCollection = $("#new-collecion");
    $(document).on("change", "#storage_type", function () {
        cancelAllAjaxRequests();
        let storage_type = $(this).val();
        let orgID = $("#orgID").val();
        $("#cloud-storage").find('.collection-card-body .accordion-container').html('');
        $("#documents-collection").find('.collection-card-body').html('');
        if (orgID == '') {
            toastr.warning("Select Organization.");
            return;
        }
        if (storage_type === '') {
            $newCollection.attr("disabled", "disabled");
            return;
        }
        $newCollection.removeAttr("disabled");

        // Wait for getClouldCollection to complete before calling getLocalCollections
        getClouldCollection(storage_type, orgID).then(function () {
            getLocalCollections(storage_type, orgID);
        }).catch(function (error) {
            console.error(error);
        });
    });
}


function createCloudAccordionItem(data) {
    let $contextMenu = `<ul>
                          <li><button class="btn-context-menu refresh-collection">Refresh</button></li>
                      </ul>`;
    let toolTipText = '';
    console.log('data:', data);
    return `
          <div class="accordion-item" id="section-${data}" data-bucket-name="${data}">
              <div class="accordion-header" title="${toolTipText}">
                  <div class="accordion-heading">
                      <i class="fa fa-bitbucket list-icon" aria-hidden="true"></i>
                      <h2 class="accordion-title">${data}</h2>
                  </div>
                  <div class="accodion-actions">
                      <button class="collapse-btn accordion-btn"><i class="fa fa-angle-down" aria-hidden="true"></i></button>
                      <button class="more-btn accordion-btn"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></button>
                  </div>
                  <div class="submenu">
                        ${$contextMenu}
                  </div>
              </div>
              <div class="accordion-content">

              </div>
          </div>`;
}

function buildLocalCollectionFiles(collectionData) {
    let $fileHtml = `<ul class="collection ui-sortable" style="min-height: 60px">`;
    $.each(collectionData, function (index, collection) {
        let sanitizedFileName = sanitizeFileName(collection.file_name);
        let iocnName = getFileIcon(collection.file_name);
        $fileHtml += ` <li class="file-item collection-item local-files"
           data-file-name="${collection.file_name}"
           data-bucket-name="${collection.bucket_sp_site_name}"
           data-file-size="${collection.size}"
           data-last-modified="${collection.last_modified}"
           data-file-id="${collection.file_id}"
           data-details-id="${collection.id}"
           >
      <div class="list-details">
      <span class="circle"><i class="${iocnName} list-icon"></i></span>
          <span class="title">${collection.file_name}</span>
          <div class="file-info">
              <span class="file-size">${formatFileSize(collection.size)}
                      KB</span>
          </div>
          <p>Last modified: <span class="file-date">${collection.last_modified_readable}</span>
          </p>
      </div>
      <div class="list-action">
      <i class="fa fa-trash-o file-delete cursor-pointer" title="Delete File" data-id="${collection.id}"></i>
      </div>
    </li>`;
    });
    $fileHtml += `</ul>`;
    return $fileHtml;
}

$(document).on("click", ".publish-collection", function (event) {
    event.preventDefault();
    let storage_type = $('#storage_type').val();
    let organization_id = $('#orgID').val();
    const storageKeys = {
        SharePoint: { key1: "sharepoint_site", key2: "folders_or_files_in_sharepoint" },
        MFiles: { key1: "vault", key2: "folders_or_files_in_vault" }
    };

    let { key1, key2 } = storageKeys[storage_type] || { key1: "bucket_name", key2: "data_folders_in_s3" };

    let $collectionWrapper = $(this).closest('.accordion-item');
    let collectionName = $collectionWrapper.attr('data-collection-name');
    let publishedCollectionName = $collectionWrapper.attr('data-published-collection-name');
    let collectionId = $collectionWrapper.find('.accordion-header').attr('data-id');
    let $files = $collectionWrapper.find('.accordion-content').find('.local-files');

    let filesMap = {};
    $files.each(function (index, file) {
        let bucketName = $(file).data('bucket-name');
        let fileName = $(file).data('file-name');

        if (!filesMap[bucketName]) {
            filesMap[bucketName] = {
                [key1]: bucketName,
                [key2]: []
            };
        }

        filesMap[bucketName][key2].push(fileName);
    });

    let files = Object.values(filesMap);
    let data = {
        collection_name: collectionName,
        file_details: files,
        storage_type: storage_type,
        org_id: organization_id,
        db_collection_id: collectionId,
        published_collection_name: publishedCollectionName
    };
    $(this).closest('.submenu').slideUp(200);

    $.ajax({
        url: '/publish-collection',
        method: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        beforeSend: function () {
            showLoader();
        },
        success: function (response) {
            // Handle success
            if (response.status === 'success') {
                $collectionWrapper.attr('data-is-cloud-collecion', 1);
                $collectionWrapper.attr('data-published-collection-name', collectionName);
                $collectionWrapper.find('.accordion-header').attr('title', 'Published');
                $collectionWrapper.removeClass('not-published');
                $collectionWrapper.find('.copy-collection').removeClass('btn-disabled').removeAttr('disabled');
                toastr.success(response.message);
            }

        },
        error: function (response) {
            hideLoader();
            // Handle error
            toastr.error(response.responseJSON.message);
        },
        complete: function () {
            hideLoader();
        }
    });
});

function createLocalAccordionItem(data) {
    //  let toolTipText = '';
    let classNotPublished = '';
    let collectionData = data.collection_data
    let collectionDecsription = data.collection_description === null ? "" : data.collection_description;
    let $collectionList = `<ul class="collection" style="min-height: 150px"> </ul>`;
    if (collectionData.length > 0) {
        $collectionList = buildLocalCollectionFiles(collectionData);
    }
    let $contextMenu = ``;
    let disableCopy = "";
    let disableClass = "";
    toolTipText = 'Published';
    if (data.is_synced === 0) {
        disableCopy = 'disabled';
        //toolTipText = 'Not Published'
        classNotPublished = 'not-published';
        disableClass = 'btn-disabled';
    }
    $contextMenu = `<ul>
        <li><button class="btn-context-menu rename-collection">Rename</button></li>
        <li><button class="btn-context-menu edit-collection-note">Edit Collection Note</button></li>
        <li><button class="btn-context-menu publish-collection">Publish</button></li>
        <li><button ${disableCopy} class="btn-context-menu copy-collection ${disableClass}">Copy</button></li>
        <li><button class="btn-context-menu delete-collection">Delete</button></li>
    </ul>`;

    return `
          <div class="accordion-item ${classNotPublished}" data-id="${data.id}" id="section-${data.id}" data-is-cloud-collecion="${data.is_cloud_collection}"
           data-published-collection-name="${data.published_collection_name}" data-collection-name="${data.collection_name}" data-collection-description="${collectionDecsription}">
              <div class="accordion-header" data-id="${data.id}" data-collection="${data.collection_name}" >
                  <div class="accordion-heading">
                      <i class="fa fa-bitbucket list-icon" aria-hidden="true"></i>
                      <h2 class="accordion-title" title="${collectionDecsription}" >${data.collection_name}</h2>
                  </div>
                  <div class="accodion-actions">
                      <button class="collapse-btn accordion-btn"><i class="fa fa-angle-down" aria-hidden="true"></i></button>
                      <button class="more-btn accordion-btn"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></button>
                  </div>
                  <div class="submenu">
                        ${$contextMenu}
                  </div>
              </div>
              <div class="accordion-content">
                    ${$collectionList}
              </div>
          </div>`;
}

function showAccordionLoader($selectoObject, customClassName) {
    let $loader = `<div class="loader-accordion ${customClassName}">
                      <i class="fa fa-gear fa-spin" style="font-size:24px"></i>
                  </div>`;
    $selectoObject.append($loader);
}

function hideAccordionLoader($selectoObject) {
    $selectoObject.find(".loader-accordion").remove();
}

function setAccordions(data) {
    return new Promise((resolve, reject) => {
        let $cloudStorage = $("#cloud-storage").find(".collection-card-body").find(".accordion-container");
        $cloudStorage.html('');

        if (data.length === 0) {
            resolve();  // If no data, resolve immediately
            return;
        }

        // Loop to append accordion items
        try {
            data.forEach(item => {
                $cloudStorage.append(createCloudAccordionItem(item));
            });

            // setTimeout(function () {
            getCollctionFiles($cloudStorage)
                .then(() => {
                    resolve();  // Resolve when getCollctionFiles is done
                })
                .catch((error) => {
                    console.error("Error in getCollctionFiles:", error);
                    reject(error);  // Reject if something goes wrong
                });
            // }, 500);
        } catch (error) {
            console.error("Error in setAccordions loop:", error);
            reject(error);  // Reject if error occurs during appending
        }
    });
}

function getCollctionFiles($cloudStorage) {
    // Create a promise that resolves when all the asynchronous operations inside the loop are done
    let promises = [];

    $.each($cloudStorage.find(".accordion-item"), function (index, bucket) {
        // Push each getClouldBucketItems call as a promise to the promises array
        let bucketName = $(bucket).attr('data-bucket-name');
        promises.push(getClouldBucketItems($(bucket), bucketName));
    });

    // Return a promise that resolves when all getClouldBucketItems promises are resolved
    return Promise.all(promises);
}

function getBucketfiles(filesData, bucketName) {

    let filesByFolder = filesData.hasOwnProperty('filesByFolder') ? filesData.filesByFolder : [];
    let collection_name = filesData.hasOwnProperty('collection_name') ? filesData.collection_name : '';
    let $fileHtml = '';
    if (filesByFolder.length === 0) {
        return $fileHtml;
    }
   // console.log(filesByFolder, collection_name);
    $.each(filesByFolder, function (folder, files) {

        if (folder !== 'root') {
            $fileHtml += `<div class="folder-name" data-folder-name="${folder}${collection_name}">
                              <i class="fa fa-folder-open-o list-icon" id="icon-${folder}${collection_name}"></i>
                       <span class="ms-2">${folder}</span>
                   </div>
                  <ul class="file-list collection" id="folder-${folder}${collection_name}">`;
        }

        $.each(files, function (key, file) {
            let fileName = file['file']['name'];
            console.log(fileName);
            //  fileName = findAndReplace(actulFileName, folder + '/', '');
            if (fileName.slice(-1) !== '/') {
                let sanitizedFileName = sanitizeFileName(bucketName + '-' + fileName);
                let iocnName = getFileIcon(fileName);
                $fileHtml += ` <li class="file-item collection-item sortable-connected"
                 data-bucket-name="${bucketName}"
                 data-file-name="${fileName}"
                  data-file-size="${file['file']['size']}"
                  data-last-modified="${file['file']['lastModifiedDateTime']}"
                   data-file-id="${sanitizedFileName}"
                   >
              <div class="list-details">
              <span class="circle"><i class="${iocnName} list-icon"></i></span>
                  <span class="title">${fileName}</span>
                  <div class="file-info">
                      <span class="file-size">${formatFileSize(file['file']['size'])}
                              KB</span>
                  </div>
                  <p>Last modified: <span class="file-date">${file['file']['last_modified_readable']}</span>
                  </p>
              </div>
              <div class="list-action">
              </div>
          </li>`;
            }
        });

        if (folder !== 'root') {
            $fileHtml += `</ul>`;
        }
    });
    return $fileHtml;
}

/**
 * Finds and replaces all occurrences of a substring in a string.
 *
 * @param {string} str - The original string.
 * @param {string} find - The substring to find.
 * @param {string} replace - The substring to replace with.
 * @returns {string} - The updated string.
 */
function findAndReplace(str, find, replace) {
    // Use a regular expression with the global flag to replace all occurrences
    const regex = new RegExp(find, 'g');
    return str.replace(regex, replace);
}

function formatFileSize(sizeInBytes) {
    return (sizeInBytes / 1024).toFixed(2); // Convert to KB and format to 2 decimal places
}

function getFileIcon(fileName) {
    // Extract the file extension
    const extension = fileName.split('.').pop().toLowerCase();

    // Default icon
    let icon = 'fa fa-file-pdf-o';

    // Determine the icon based on the file extension
    if (extension === 'pdf') {
        icon = 'fa fa-file-pdf-o';
    } else if (['doc', 'docx', 'dot', 'dotx', 'txt'].includes(extension)) {
        icon = 'fa-file-word-o';
    } else if (['ppt', 'pptx', 'pps', 'ppsx', 'pot', 'potx', 'odp'].includes(extension)) {
        icon = 'fa-file-powerpoint-o';
    }

    return icon;
}

function initDragable() {
    $("#cloud-storage .accordion-content li").draggable({
        helper: function () {
            let $original = $(this);
            let $clone = $original.clone();

            // Properties to copy
            let properties = [
                "width", "height", "background", "color",
                "padding", "margin", "border"
            ];

            // Copy each property individually
            properties.forEach(property => {
                $clone.css(property, $original.css(property));
            });

            return $clone;
        },
        revert: "valid",
        cursor: "move",
        cursorAt: {
            top: 56,
            left: 56
        },
        // connectToSortable: "#documents-collection .accordion-content ul",
        connectToSortable: ".accordion-item .ui-sortable",
        start: function () {
            // Highlight the original item when dragging starts
            $(this).addClass("dragging");
        },
        stop: function () {
            // Remove the highlight after dragging stops
            $(this).removeClass("dragging");
            $(".ui-sortable").removeClass("highlight-dropzone"); // Remove highlight from all drop areas
        },
    });
}

$("#documents-collection").on("dragover", ".accordion-item .ui-sortable", function (event) {
    event.preventDefault();
    $(".ui-sortable").removeClass("highlight-dropzone"); // Remove highlight from other accordions
    $(this).addClass("highlight-dropzone"); // Highlight only the hovered drop area
}).on("dragleave", ".accordion-item .ui-sortable", function () {
    $(this).removeClass("highlight-dropzone"); // Remove highlight when leaving
}).on("drop", ".accordion-item .ui-sortable", function () {
    $(this).removeClass("highlight-dropzone"); // Remove highlight after drop
});

function updateDropZoneMessage($dropZone) {
    if ($dropZone.children("li").length === 0) {
        if ($dropZone.find(".drop-message").length === 0) {
            $dropZone.append('<div class="drop-message">Drag and Drop files here</div>');
        }
    } else {
        $dropZone.find(".drop-message").remove();
    }
}


function sanitizeFileName(fileName) {

    // Replace non-alphanumeric characters with dashes
    let sanitized = fileName.replace(/[^a-zA-Z0-9-_]+/g, '-');

    // Remove consecutive dashes and trim dashes from the beginning and end
    sanitized = sanitized.replace(/-+/g, '-').toLowerCase().trim('-');

    return sanitized;
}

function getClouldBucketItems($clickedObject, bucketName) {
    return new Promise((resolve, reject) => {
        // Perform your async operations for each bucket, then resolve or reject the promise accordingly
        try {
            $clickedObject.find('.accordion-content').html('')
            let data = {
                org_id: $("#orgID").val(),
                bucketName,
                serviceprovider: $('#storage_type').val(),
            };

            $.ajax({
                type: "post",
                url: "/getbucketitems",
                data: data,
                beforeSend: function () {
                    showAccordionLoader($clickedObject);
                },
                success: function (response) {
                    hideAccordionLoader($clickedObject);
                    $files = getBucketfiles(response.data, bucketName)
                    $clickedObject.find('.accordion-content').html($files);
                    initDragable();
                },
                error: function (xhr, status, error) {
                    hideAccordionLoader($clickedObject);
                    reject();
                },
                complete: function () {
                    hideAccordionLoader($clickedObject);
                    $('input[name="storage_type"]').prop('disabled', false);
                    resolve();
                }
            });

        } catch (error) {
            console.error(`Error initializing Collection: ${error}`);
            reject();
        }
    });
}

function getClouldBucketItemsOld($clickedObject, bucketName) {
    try {
        $clickedObject.find('.accordion-content').html('')
        let data = {
            org_id: $("#orgID").val(),
            bucketName,
            serviceprovider: $('#storage_type').val(),
        };

        $.ajax({
            type: "post",
            url: "/getbucketitems",
            data: data,
            beforeSend: function () {
                showAccordionLoader($clickedObject);
            },
            success: function (response) {
                hideAccordionLoader($clickedObject);
                $files = getBucketfiles(response.data, bucketName)
                $clickedObject.find('.accordion-content').html($files);
                initDragable()
                getFileDifferences();
                initSortable();
            },
            error: function (xhr, status, error) {
                hideAccordionLoader($clickedObject);
            },
            complete: function () {
                hideAccordionLoader($clickedObject);
                $('input[name="storage_type"]').prop('disabled', false);
            }
        });

    } catch (error) {
        console.error(`Error initializing Collection: ${error}`);
    }
}


function toggleAccordion() {
    // Detect if the .accordion-content is displayed or hidden
    $(document).on('click', '.collapse-btn', function () {
        let content = $(this).closest('.accordion-header').next('.accordion-content');
        let parentContainer = $(this).closest('.collection-card-body');
        let icon = $(this).find('i');
        let displayValue = content.css('display');
        // Check the display property
        parentContainer.find('.accordion-header').removeClass('show-accordion').next('.accordion-content').slideUp();
        if (displayValue === 'none') {
            $(this).closest('.accordion-header').addClass('show-accordion');
            icon.removeClass('fa-angle-down').addClass('fa-angle-up');
            content.slideDown(600);
            getFileDifferences();
        } else {
            $(this).closest('.accordion-header').removeClass('show-accordion');
            icon.removeClass('fa-angle-up').addClass('fa-angle-down');
            content.slideUp(600);
        }
    });
}


function expandAllAccordions($selector) {
    $selector.find('.accordion-content').css('display', 'block');
    $selector.find('.accordion-header').removeClass('show-accordion');
    $selector.find('.accordion-header').find('.collapse-btn > i').removeClass('fa-angle-down').addClass('fa-angle-up');
}

function initContextMenu() {
    // Show submenu on more button click
    $(document).on('click', '.more-btn', function (e) {
        e.stopPropagation(); // Prevents the accordion header click from triggering
        var submenu = $(this).closest('.accodion-actions').next('.submenu'); // Get the submenu next to the button

        // Hide all other submenus
        $('.submenu').not(submenu).slideUp(600);

        // Position the submenu next to the button
        var buttonOffset = $(this).offset();
        submenu.css({
            top: $(this).closest('.accordion-header').outerHeight(),
            right: 0
        });

        // Toggle the current submenu
        submenu.stop(true, true).slideToggle(600);
    });
}


// Close submenu if clicking outside
$(document).click(function (e) {
    if (!$(e.target).closest('.accordion-item').length) {
        $('.submenu').slideUp(600);
    }
});
