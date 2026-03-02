<option value="" selected>Select Units</option>
@foreach($building_units as $building_unit)
<option value="{{ $building_unit->unit->id }}">{{ $building_unit->unit->unit_code }}</option>
@endforeach