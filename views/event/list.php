<?php

$this->title = 'События';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="lease-default-index" data-ng-controller="EventController">
    <div>
        <h1>All Leases</h1>
        <div data-ng-show="events.length > 0">
            <table class="table table-striped table-hover">
                <thead>
                <th>event_date</th>
                <th>child_id</th>
                <th>user_id</th>
                <th>title</th>
                <th>post_text</th>
                </thead>
                <tbody>
                <tr data-ng-repeat="Event in events">
                    <td>{{Event.event_date}}</td>
                    <td>{{Event.child_id}}</td>
                    <td>{{Event.username}}</td>
                    <td>{{Event.title}}</td>
                    <td>{{Event.post_text}}</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div data-ng-show="Events.length == 0">
            No results
        </div>
    </div>
</div>
