<option value="" selected>Select Amenity</option>
@foreach($building_amenity as $building_amenity)
<option value="{{ $building_amenity->amentityType->id }}">{{ $building_amenity->amentityType->amentity_types_name }}</option>
@endforeach