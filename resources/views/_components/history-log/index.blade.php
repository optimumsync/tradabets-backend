

    <table class="table table-responsive-lg table-bordered table-striped mb-0 has-action-clm" id="datatable-details">
        <thead>
            <tr>
                <th class="is-status">Action</th>
                <th class="is-status">Fields Edited</th>
                <th>Actioned By</th>
                <th class="is-datetime">Date</th>
                <th class="is-status">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($history_log as $row)
            <tr data-rowdetails='{{ $row->previous_merged_json_data }}'>
                <td>{{ $row->action_description }}</td>
                <td>{{ $row->num_data_fields }}</td>
                <td>{{ $row->user_full_name }}</td>
                <td>{{ $row->created_at }}</td>
                <td>{{ $row->status_description }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
