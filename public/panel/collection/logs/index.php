<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <style>
        td.details-control {
            text-align: center;
            cursor: pointer;
        }

        tr.shown td.details-control {
            text-align: center;
        }

        pre {
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .dataTables_empty {
            height: 35px;
            font-size: 12px;
        }
        #auditLogsTable_length select {
            display: inline-block;
            width: auto;
            height: 34px;
            padding: 6px 14px;
            font-size: 14px;
            line-height: 1.42857143;
            color: #000;
            background-color: #fff;
            background-image: none;
            border: 1px solid #cfd0d2;
            border-radius: 4px;
            -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
            -webkit-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
            -o-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
            transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
        }

        #auditLogsTable_length label,
        #auditLogsTable_filter label {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        /* table.dataTable.no-footer {
            border-bottom: none !important;
        }
        table.dataTable {
            border-bottom: none !important;
        } */
        #auditLogsTable_filter input {
            display: block;
            width: 100%;
            height: 34px;
            padding: 6px 14px;
            font-size: 14px;
            line-height: 1.42857143;
            color: #000;
            background-color: #fff;
            background-image: none;
            border: 1px solid #cfd0d2;
            border-radius: 4px;
            -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075);
            -webkit-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
            -o-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
            transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
        }
    </style>
</head>

<body>
    <div class="">
        <table id="auditLogsTable" class="table table-hover" style="width:100%">
            <thead class="thead-dark">
                <tr>

                    <th>Event Name</th>
                    <th>Actions</th>
                    <th>User ID</th>
                    <th>IP Address</th>
                    <th>Event Date</th>
                    <th>Event Type</th>
                </tr>
            </thead>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            var table = $('#auditLogsTable').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "/audit-logs/data",
                    "type": "GET"
                },
                "columns": [
                    {
                        "data": "description"
                    },
                    {
                        "data": "action_description"
                    },
                    {
                        "data": "user_id"
                    },
                    {
                        "data": "ip_address"
                    },
                    {
                        "data": "created_at"
                    },
                    {
                        "data": "action_type",
                        "orderable": false
                    }
                ],
                "order": [
                    [4, 'desc']
                ]
            });


        });
    </script>
</body>

</html>
