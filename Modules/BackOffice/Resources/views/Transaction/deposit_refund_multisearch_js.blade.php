<script>
	$(document).on('change',".receipts_generation_receipt", function(){
		var receiptNo = $("#receipts_generation_receipt").val();
//alert(id);
var selected = '';
if(receiptNo !=""){ 
	$.ajax({
		method: "POST",
		url: "{{route('getReceiptDetails')}}",
		data: { receiptNo: receiptNo, 
			"_token" : $('meta[name="csrf-token"]').attr('content')},
			success: function(data){
				var results = $.parseJSON(data);
			//	alert(results['tenant_contract_start_date']);
			var from = convertDate(results[0].tenant_contract_start_date); 
			var to = convertDate(results[0].tenant_contract_valid_to_date);

			$('#deposit_refund_valid_from').val(results[0].tenant_contract_start_date); 
			$('#deposit_refund_valid_from1').val(from); 
			$('#deposit_refund_valid_to').val(results[0].tenant_contract_valid_to_date); 
			$('#deposit_refund_valid_to1').val(to);

			$('#tenant_contract_no').val(results[0].tenant_contract_no);  
			$('#tenant_contract_id').val(results[0].id);

			$('#first_row1 .debit_amount').val(formatNumber(parseFloat(results[1].receipts_generation_amt).toFixed(3)));
			$('#first_row1 .credit_amount').val(0); 
			$('#receipts_generation_id').val(results[1].id);
			$('#debit_amount_total').val(formatNumber(parseFloat(results[1].receipts_generation_amt).toFixed(3))); 
			$('#deposit_refund_amt').val(formatNumber(parseFloat(results[1].receipts_generation_amt).toFixed(3)));
			$('#debit_amount_first').val(formatNumber(parseFloat(results[1].receipts_generation_amt).toFixed(3))); 
			$('#credit_amount_first').val(0); 
			$('#credit_amount_second').val(formatNumber(parseFloat(results[1].receipts_generation_amt).toFixed(3))); 
			$('#debit_amount_second').val(0); 
			$('#deposit_refund_comment').text('Refund of Deposit Receipt :'+results[2].building_name+'/'+results[3].unit_code+'/'+results[4].tenant_name+'@'+formatNumber(parseFloat(results[1].receipts_generation_amt).toFixed(3)));	 

			$(".read").attr('readonly',true); 
			var validator = $( "#payment-form" ).validate();
			validator.form();
		}
	}); 
}
});
	/********** Building unit tenant change ******************************/
	$(document).on('change',".tenants_id", function(){
		var building_id = $("#building_id").val();
		var tenant = $("#tenants_id").val();
		var buildings_id = $("#buildings_id").val();
		var id;
		if(building_id !=""){
			id = building_id;
		}
		else{
			id = buildings_id;
		}
//alert(id);
var tenant_code = $('option:selected', this).attr('tenant_code'); 
$("#tenant_code").val(tenant_code);
var selected = '';
var building = $("#building_id").val();
if(id == null){id = $("#building_id").val();}
var unit = $("#unit_id").val() || $("#unit_ids").val();
if(id !="" && unit != ""){
	$('.receipts_generation_receipt_no').hide();
	$('.receipts_generation_receipt').show(); 
	$.ajax({
		method: "POST",
		url: "{{route('getReceiptDetailsByBuildingUnitTenant')}}",
		data: {"building":id,"unit":unit,"tenant":tenant,"_token": "{{ csrf_token() }}"},
		cache: false,
		dataType: "json",
		success: function(data){
			if(data.length > 0){
				if(data.length ==1){selected = "selected";}
				$('#receipts_generation_receipt').empty();
				$('#receipts_generation_receipt').append('<option value = "">'+ 'Select Receipt' +'</option>')
				$.each(data, function(key, value) {
					$('#receipts_generation_receipt').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['receipts_generation_receipt_no'] +'</option>');
				});
				if(data.length ==1) $( "#receipts_generation_receipt" ).trigger( "change" );
			}
			else{
				$('#receipts_generation_receipt').html('<option value="">No Available Receipts</option>');
			}

		}
	}); 
}
});
	/************************* tenant change ******************************/
	$(document).on('change',".tenant_id", function(){
		var building_id = $("#building_id").val();
		var tenant = $("#tenant_id").val();
		var buildings_id = $("#buildings_id").val();
		var id;
		if(building_id !=""){
			id = building_id;
		}
		else{
			id = buildings_id;
		}
//alert(id);
var selected = '';
var building = $("#building_id").val();
if(id == null){id = $("#building_id").val();}
var unit = $("#unit_id").val() || $("#unit_ids").val();
if(id !="" && unit != ""){
	$('.receipts_generation_receipt_no').hide();
	$('.receipts_generation_receipt').show(); 
	$.ajax({
		method: "POST",
		url: "{{route('getReceiptDetailsByBuildingUnitTenant')}}",
		data: {"building":id,"unit":unit,"tenant":tenant,"_token": "{{ csrf_token() }}"},
		cache: false,
		dataType: "json",
		success: function(data){
			if(data.length > 0){
				if(data.length ==1){selected = "selected";}
				$('#receipts_generation_receipt').empty();
				$('#receipts_generation_receipt').append('<option value = "">'+ 'Select Receipt' +'</option>')
				$.each(data, function(key, value) {
					$('#receipts_generation_receipt').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['receipts_generation_receipt_no'] +'</option>');
				});
				if(data.length ==1) $( "#receipts_generation_receipt" ).trigger( "change" );
			}
			else{
				$('#receipts_generation_receipt').html('<option value="">No Available Receipts</option>');
			}

		}
	}); 
}
});
	/******************************** Unit Change for Tenant auto**********************/
	$(document).on('change',".units_id", function(){
		
		var selected = '';
		$('.building-name2').show();
		$('.building_id').hide(); 
		var tenant = $("#tenant_id").val();
		var unit_no = $('option:selected', this).attr('unit_no'); 
		$("#unit_no").val(unit_no);
		var unit = $("#units_id").val();
		if(unit != ""){
			$('.receipts_generation_receipt_no').hide();
			$('.receipts_generation_receipt').show(); 
			$.ajax({
				method: "POST",
				url: "{{route('getReceiptDetailsByUnitTenant')}}",
				data: {"unit":unit,"tenant":tenant,"_token": "{{ csrf_token() }}"},
				cache: false,
				dataType: "json",
				success: function(data){
					if(data.length > 0){
						if(data.length ==1){selected = "selected";}
						$('#receipts_generation_receipt').empty();
						$('#receipts_generation_receipt').append('<option value = "">'+ 'Select Receipt' +'</option>')
						$.each(data, function(key, value) {
							$('#receipts_generation_receipt').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['receipts_generation_receipt_no'] +'</option>');
						});
						if(data.length ==1) $( "#receipts_generation_receipt" ).trigger( "change" );
					}
					else{
						$('#receipts_generation_receipt').html('<option value="">No Available Receipts</option>');
					}

				}
			}); 

			$.ajax({
				method: "POST",
				url: "{{route('buildingDetailsByUnitId')}}",
				data: {"id":unit,"_token": "{{ csrf_token() }}"},
				cache: false,
				dataType: "json",
				success: function(data){
					$('#building_name').val(data.building_name);
					$('#financial_building_code').val(data.building_name);
					$('#building_code').val(data.building_code);
					$('#buildings_id').val(data.id);
					$('.dim2').val(data.building_name); 
				}
			});
		}
	});
	/*********************** Building Unit Change**********************/
	$(document).on('change',".unit_id", function(){
		var building_id = $("#building_id").val();
		var buildings_id = $("#buildings_id").val();
		var id;
		if(building_id !=""){
			id = building_id;
		}
		else{
			id = buildings_id;
		}
		//alert(id);
		var selected = '';
		var building = $("#building_id").val();
		if(id == null){id = $("#building_id").val();}
		var unit = $("#unit_id").val();
		var unit_no = $('option:selected', this).attr('unit_no'); 
		$("#unit_no").val(unit_no);
		if(id !="" && unit != ""){
			$('.tenant_name').hide();
			$('.tenants_id').show(); 
			$.ajax({
				method: "POST",
				url: "{{route('getTenantDetailsByBuildingUnit')}}",
				data: {"building":id,"unit":unit,"_token": "{{ csrf_token() }}"},
				cache: false,
				dataType: "json",
				success: function(data){
					if(data.length > 0){
						if(data.length ==1){selected = "selected";}
						$('#tenants_id').empty();
						$('#tenants_id').append('<option value = "">'+ 'Select Tenant' +'</option>')
						$.each(data, function(key, value) {
							$('#tenants_id').append('<option tenant_code="'+value['tenant_code'] +'" value="'+ value['id'] +'" '+ selected +'>'+ value['tenant_name'] +'</option>');
						});
						if(data.length ==1) $( "#tenants_id" ).trigger( "change" );
					}
					else{
						$('#tenants_id').html('<option value="">No Available Tenants</option>');
					}

				}
			}); 
		}
	});
	/*************************** Unit *****************************************/
	$('#unit_code').autocomplete({
		source : '{!!URL::route('getUnitAutocompleteCode')!!}',
		minlenght:2,
		autoFocus:true,
		change:function(e,ui){
			if (ui.item == null || ui.item == undefined) {
				$("#unit_code").val('');
				$('#create_build_span').hide();
				$('#vendor_name-error').show();
			}else {
				var unitcode = $('#unit_code').val(); 
				$('#unit_no').val(ui.item.code);   
				$('#unit_ids').val(ui.item.ids);   
				var selected = '';
				$('.building-name2').show();
				$('.building_id').hide(); 
				$.ajax({
					method: "POST",
					url: "{{route('buildingDetailsByUnitId')}}",
					data: {"id":ui.item.ids,"_token": "{{ csrf_token() }}"},
					cache: false,
					dataType: "json",
					success: function(data){
						$('#building_name').val(data.building_name);
						$('#financial_building_code').val(data.building_name);
						$('#building_code').val(data.building_code);
						$('#buildings_id').val(data.id);
						$('.dim2').val(data.building_name); 
					}
				}); 

				var unit = $('#unit_ids').val();
				//alert(unit);
				$('.tenant_name').hide();
				$('.tenants_id').show(); 
				$.ajax({
					method: "POST",
					url: "{{route('getTenantDetailsByUnitId')}}",
					data: {"unit":unit,"_token": "{{ csrf_token() }}"},
					cache: false,
					dataType: "json",
					success: function(data){
						if(data.length > 0){
							if(data.length ==1){selected = "selected";}
							$('#tenants_id').empty();
							$('#tenants_id').append('<option value = "">'+ 'Select Tenant' +'</option>')
							$.each(data, function(key, value) {
								$('#tenants_id').append('<option tenant_code="'+value['tenant_code'] +'" value="'+ value['id'] +'" '+ selected +'>'+ value['tenant_name'] +'</option>');
							});
							if(data.length ==1) $( "#tenants_id" ).trigger( "change" );
						}
						else{
							$('#tenants_id').html('<option value="">No Available Tenants</option>');
						}

					}
				}); 
 
	}

}
});	
</script>