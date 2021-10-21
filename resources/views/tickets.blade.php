<?php
use Kordy\Ticketit\Models\Ticket;

$collection = Ticket::active();
$collection
->join('users', 'users.id', '=', 'ticketit.user_id')
->join('ticketit_statuses', 'ticketit_statuses.id', '=', 'ticketit.status_id')
->join('ticketit_priorities', 'ticketit_priorities.id', '=', 'ticketit.priority_id')
->join('ticketit_categories', 'ticketit_categories.id', '=', 'ticketit.category_id')
->select([
    'ticketit.id',
    'ticketit.subject AS subject',
    'ticketit_statuses.name AS status',
    'ticketit_statuses.color AS color_status',
    'ticketit_priorities.color AS color_priority',
    'ticketit_categories.color AS color_category',
    'ticketit.id AS agent',
    'ticketit.updated_at AS updated_at',
    'ticketit.created_at AS created_at',
    'ticketit_priorities.name AS priority',
    'users.name AS owner',
    'ticketit.agent_id',
    'ticketit_categories.name AS category',
    'ticketit.client as client',
    'ticketit.position as position',
    'ticketit.created_at AS created_string_at',
    'ticketit.updated_at AS updated_string_at',
])->with('agent_user');
?>
<style type="text/css">
	table {
	  border-collapse: collapse;
	  width: 100%;
	}
	table, th, td {
	  border: 1px solid black;
	}
	th {
	  background-color: #2A5180;
	  color: white;
	}
	tr:nth-child(even) {background-color: #f2f2f2;}
</style>
<!DOCTYPE html>
<table>
	<h4>Los siguientes tiquetes se encuentran activos:</h4>
	<br/>
	<thead>
		<tr>
			<th># Ticket</th>
			<th>Estado</th>
			<th>Asunto</th>
			<th>Agente</th>
			<th>Cliente</th>
			<th>Puesto</th>
			<th>Fecha de creación</th>
			<th>Prioridad</th>
			<th>Dueño</th>
			<th>Categoría</th>
		</tr>
	</thead>
	<tbody>
		@foreach($collection->get() as $item)
		<tr>
			<td>{{$item->id}}</td>
			<td>{{$item->status}}</td>
			<td><a href="http://tickets.rmla.co/tickets/{{$item->id}}">{{$item->subject}}</a></td>
			<td>{{$item->agent_user->name}}</td>
			<td>{{$item->client}}</td>
			<td>{{$item->position}}</td>
			<td>{{$item->created_at}}</td>
			<td>{{$item->priority}}</td>
			<td>{{$item->owner}}</td>
			<td>{{$item->category}}</td>
		</tr>
		@endforeach
	</tbody>
</table>