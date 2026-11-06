@props(['headers', 'rows', 'total' => null])
<div class="table-scroll"><table class="purchase-table"><thead><tr>@foreach($headers as $header)<th>{{ $header }}</th>@endforeach</tr></thead><tbody>
@foreach($rows as $row)
<tr data-searchable="{{ implode(' ', $row) }}">
@foreach($row as $cell)<td>@if(in_array($cell, ['Pending','Received','Confirmed','Partial','Active']))<span class="badge {{ $cell === 'Pending' ? 'amber' : ($cell === 'Partial' ? 'sky' : 'green') }}">{{ $cell }}</span>@else{{ $cell }}@endif</td>@endforeach
</tr>
@endforeach
</tbody>@if($total)<tfoot><tr><th colspan="{{ count($headers) - 1 }}">Total Pending</th><th>{{ $total }}</th></tr></tfoot>@endif</table></div>
