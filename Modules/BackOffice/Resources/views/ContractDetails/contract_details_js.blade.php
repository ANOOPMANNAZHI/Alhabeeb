<script>
	$(document).ready(function() {

		$(document).on('change keyup paste','#building',function(){ 

			var building = $("#building").val();
			var unit = $("#unit").val();
			var tenant = $("#tenant_id").val();

			if(building==""){
				$("#agreementDetail").empty();
				$("#unit_id").hide();
				$("#tenant_id").hide();
				$("#unit").show();
				$("#unit").val("");
				$("#tenant").show();
				$("#tenant").val("");
			}else{
				$("#agreementDetail").show();
			}

		});
		$(document).on('change keyup paste','#unit',function(){ 

			var building = $("#building").val();
			var unit = $("#unit").val();
			var tenant = $("#tenant_id").val();

			if(unit == ""){
				$("#agreementDetail").empty();
				$("#tenant_id").hide();
				$("#building").val("");
				$("#tenant").show();
				$("#tenant").val("");
			}else{
				$("#agreementDetail").show();
			}

		});

		$(document).on('change keyup paste','#tenant',function(){ 

			var building = $("#building").val();
			var unit = $("#unit").val();
			var tenant = $("#tenant").val();

			if(tenant == ""){
				$("#agreementDetail").empty();
				$("#unit_id").hide();
				$("#building").val("");
				$("#unit").show();
				$("#unit").val("");
			}else{
				$("#agreementDetail").show();
			}

		});


		/***************************Building*********************************/
		$('#building').autocomplete({
			source : '{!!URL::route('allBuildingsAutocomplete')!!}',
			minlenght:2,
			autoFocus:true,
			select:function(e,ui){
				if(ui.item.ids != null){
					$('#building_id').val(ui.item.ids);
					var building_id = ui.item.ids;
					var building = $('#building').val();

					if(building_id != "" && building != ""){
						$('#unit').hide();
						$('#units_id').hide();
						$('#unit_id').show();
						$.ajax
						({
							type: "POST",
							url: "{{route('allUnitsDetail')}}",
							data: {"building_id":building_id,"_token": "{{ csrf_token() }}"},
							cache: false,
							success: function(data)
							{
								var result = $.parseJSON(data);
								$("#agreementDetail").empty();
								//$("#tenant_id").val("");
								selected = "";
								if(result[0].length > 0){
									if(result[0].length ==1){selected = "selected";}
									$('#unit_id').empty();
									$('#unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
									$.each(result[0], function(key, value) {
										$('#unit_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['unit_no'] +'</option>');
									}); 
									var unit_id = $('#unit_id').val();
									if(building_id != "" && unit_id != ""){
										$('#tenant').hide();
										$('#tenant_id').show();
										$.ajax
										({
											type: "POST",
											url: "{{route('allTenantsDetail')}}",
											data: {"building_id":building_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
											cache: false,
											success: function(data)
											{
												var result = $.parseJSON(data);
												$("#agreementDetail").empty();
												selected = "";
												if(result[0].length > 0){
													if(result[0].length ==1){selected = "selected";}
													$('#tenant_id').empty();
													$('#tenant_id').append('<option value="">'+ 'Select Tenant' +'</option>')
													$.each(result[0], function(key, value) {
														$('#tenant_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['tenant_name'] +'</option>');
													});  

													var tenant_id = $('#tenant_id').val();

													if(building_id != "" && tenant_id != "" && unit_id != ""){

														$.ajax
														({
															type: "POST",
															url: "{{route('contractDetails')}}",
															data: {"building_id":building_id,"tenant_id":tenant_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
															cache: false,
															success: function(data)
															{
																$("#agreementDetail").html(data);
															} 
														});
													} 
													

												} 
												
											}
										});

									}          
								} else{
									$('#unit_id').html('<option value="">No Available Units</option>');
								}
							}
						});

					} 

				}

			}
		});



		/***********************************UNit Change*******************************/
		$("#unit_id").on('change input',function(e){
			var building_id = $('#building_id').val();
			var unit_id = $("#unit_id").val();
			var tenant_id = $("#tenant_id").val();
			if(building_id != "" && unit_id != ""){
				$('#tenant').hide();
				$('#tenant_id').show();
				$.ajax
				({
					type: "POST",
					url: "{{route('allTenantsDetail')}}",
					data: {"building_id":building_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
					cache: false,
					success: function(data)
					{
						var result = $.parseJSON(data);
						$("#agreementDetail").empty();
						selected = "";
						if(result[0].length > 0){
							if(result[0].length ==1){selected = "selected";}
							$('#tenant_id').empty();
							$('#tenant_id').append('<option value="">'+ 'Select Tenant' +'</option>')
							$.each(result[0], function(key, value) {
								$('#tenant_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['tenant_name'] +'</option>');
							});  
							if($('#tenant_id').val() == "")
							{
								var tenant_id = $('#tenants_id').val();
							}else{
								var tenant_id = $('#tenant_id').val();
							}
							if(building_id != "" && tenant_id != "" && unit_id != ""){
								
								$.ajax
								({
									type: "POST",
									url: "{{route('contractDetails')}}",
									data: {"building_id":building_id,"tenant_id":tenant_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
									cache: false,
									success: function(data)
									{
										$("#agreementDetail").html(data);
									} 
								});
							}          
						} else{
							$('#tenant_id').html('<option value="">No Available Tenants</option>');
							if(building_id != ""  && unit_id != ""){
								
								$.ajax
								({
									type: "POST",
									url: "{{route('contractDetails')}}",
									data: {"building_id":building_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
									cache: false,
									success: function(data)
									{
										$("#agreementDetail").html(data);
									} 
								});
							} 
						}
					}
				});

			}
			if($('#tenant_id').val() == "")
			{
				var tenant_id = $('#tenants_id').val();
			}else{
				var tenant_id = $('#tenant_id').val();
			}
			if(tenant_id != "" && unit_id != ""){

				$.ajax
				({
					type: "POST",
					url: "{{route('contractDetails')}}",
					data: {"tenant_id":tenant_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
					cache: false,
					success: function(data)
					{
						$("#agreementDetail").html(data);
					} 
				});
				$.ajax
				({
					type: "POST",
					url: "{{route('getBuildingCompleteDetails')}}",
					data: {"tenant_id":tenant_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
					cache: false,
					success: function(data)
					{
						var result = $.parseJSON(data);
						$("#building").val(result[0].building_name);
						$("#building_id").val(result[0].id);
					}
				});
			}


		}); 

		/***********************************Tenant Change*******************************/
		$("#tenant_id").on('change input',function(e){
			var building_id = $('#building_id').val();

			if($("#unit_id").val() == "")
				var unit_id = $("#units_id").val();
			else
				var unit_id = $("#unit_id").val();

			if($("#tenant_id").val() == "")
				var tenant_id = $("#tenants_id").val();
			else
				var tenant_id = $("#tenant_id").val();

			$('#tenants_id').hide();
			$('#tenant_id').show();
			$('#building_id').show();
			if(building_id != "" && tenant_id != "" && unit_id != ""){
				$.ajax
				({
					type: "POST",
					url: "{{route('contractDetails')}}",
					data: {"building_id":building_id,"tenant_id":tenant_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
					cache: false,
					success: function(data)
					{
						$("#agreementDetail").html(data);
					} 
				});
			}          

		}); 


		/***************************UNit  autocomplete**********************************/
		$('#unit').autocomplete({
			source : '{!!URL::route('allUnitsAutocomplete')!!}',
			minlenght:2,
			autoFocus:true,
			select:function(e,ui){
				if(ui.item.ids != null){
					$('#units_id').val(ui.item.ids);
					var unit_id = ui.item.ids;
					$('#tenant').hide();
					$('#tenant_id').show();
					if(unit_id){
						$.ajax
						({
							type: "POST",
							url: "{{route('getBuildingDetails')}}",
							data: {"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
							cache: false,
							success: function(data)
							{
								var result = $.parseJSON(data);
								selected = "";
								if(result[0].length > 0){
									if(result[0].length ==1){selected = "selected";}
									$('#tenant_id').empty();
									$('#tenant_id').append('<option value="">'+ 'Select Tenant' +'</option>')
									$.each(result[0], function(key, value) {
										$('#tenant_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['tenant_name'] +'</option>');
									});             

									$("#building").val(result[1].building_name);
									$("#building_id").val(result[1].id);

									var tenant_id = $('#tenant_id').val();
									if(tenant_id != "" && unit_id != ""){

										$.ajax
										({
											type: "POST",
											url: "{{route('contractDetails')}}",
											data: {"tenant_id":tenant_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
											cache: false,
											success: function(data)
											{
												$("#agreementDetail").html(data);
											} 
										});
									}
								}else{
									$("#building").val(result[1].building_name);
									$("#building_id").val(result[1].id);
									$('#tenant_id').html('<option value="">No Available Tenants</option>');

									var building_id = $('#building_id').val();
									if(tenant_id != "" && unit_id != ""){

										$.ajax
										({
											type: "POST",
											url: "{{route('contractDetails')}}",
											data: {"building_id":building_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
											cache: false,
											success: function(data)
											{
												$("#agreementDetail").html(data);
											} 
										});
									}
								} 
							}
						});

					}
				}

			}
		});

		/***************************Tenant  autocomplete**********************************/
		$('#tenant').autocomplete({
			source : '{!!URL::route('allTenantsAutocomplete')!!}',
			minlenght:2,
			autoFocus:true,
			select:function(e,ui){
				if(ui.item.ids != null){
					$('#tenants_id').val(ui.item.ids);
					var tenant_id = ui.item.ids;
					$('#unit').hide();
					$('#unit_id').show();
					if(tenant_id){
						$.ajax
						({
							type: "POST",
							url: "{{route('getUnitDetails')}}",
							data: {"tenant_id":tenant_id,"_token": "{{ csrf_token() }}"},
							cache: false,
							success: function(data)
							{
								var result = $.parseJSON(data);
								selected = "";
								if(result[0].length > 0){
									if(result[0].length ==1){selected = "selected";}
									$('#unit_id').empty();
									$('#unit_id').append('<option value="">'+ 'Select Unit' +'</option>')
									$.each(result[0], function(key, value) {
										$('#unit_id').append('<option value="'+ value['id'] +'" '+ selected +'>'+ value['unit_code'] +'</option>');
									});             
								} 
								

								$("#building").val(result[1].building_name);
								$("#building_id").val(result[1].id);

								var unit_id = $('#unit_id').val();
								if(tenant_id != "" && unit_id != ""){

									$.ajax
									({
										type: "POST",
										url: "{{route('contractDetails')}}",
										data: {"tenant_id":tenant_id,"unit_id":unit_id,"_token": "{{ csrf_token() }}"},
										cache: false,
										success: function(data)
										{
											$("#agreementDetail").html(data);
										} 
									});
								}  
							}
						});

					}
				}

			}
		});
		/**********************************************************************/
		/**********************************************************************/
	});


    function save_remark(ths){

    	   // $(ths).attr("disabled","true");

    	    var building = $("#building_id").val();
			var unit     = $("#unit_id").val() || $("#units_id").val();
			var tenant   = $("#tenant_id").val() || $("#tenants_id").val();
			var remark   = $("#remark_sec").val();
			$("#msg").css("display","block");


			$.ajax
				({
					type: "POST",
					dataType: 'json',
					url: "{{route('GetSummaryRemark')}}",
					data: {"building_id":building,"tenant_id":tenant,"unit_id":unit,"remark": remark,"_token": "{{ csrf_token() }}"},
					cache: false,
					success: function(data)
					{
                        var respo = '<div class="alert alert-success alert-dismissible fade show " role="alert" style="color:black;">'+data+'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>'

						$("#msg").html(respo);
					} 
				});


    }

    function open_prompt(tcid){
    	if (confirm("are you sure?")) {
          $.ajax
				({
					type: "POST",
					dataType: 'json',
					url: "{{route('oldcontractmovetoLeagal')}}",
					data: {"tenant_contract_id":tcid,"_token": "{{ csrf_token() }}"},
					cache: false,
					success: function(data)
					{
                       alert(data);
					} 
				});
		  

		} 
    }

</script>
