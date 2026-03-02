@extends('layouts.plms-app')


 
@section('content')


<!-- start page content -->           
                    <div class="page-bar">
                        <div class="page-title-breadcrumb">
                            <div class=" pull-left">
                                <div class="page-title">Assign Permission</div>
                            </div>
                             {{ Breadcrumbs::render('rolePermission',$role) }}
                        </div>
                    </div>
<!-- sales lead window-->
   <div class="row">
       <div class="col-md-12 col-sm-12">
        <div class="card  card-box">          
            <div class="card-body ">

                  <div class="dataSearchBox">
                      <form method="post"  class="menu-form" id="menugroup-form" action="{{route('storePermissions',$role->id)}}" >
                        {{csrf_field()}}                          
                        
                          <div class="row sep-div">
                                 <div class="col-sm-6">
                                      <div class="form-group">
                                          <label for="name">Menu</label>
                                           <div class="p-relative">
												<i class="fa fa-bars icn-add" aria-hidden="true"></i>
                                          <select name="menu" id="menuoption" class="form-control">
                                            <option value="">Select Menu </option>
                                            @foreach($menu as $val)
                                            <option value="{{$val->id}}">{{$val->menu_name}}</option>
                                            @endforeach
                                          </select>                                         
                                      </div>
                                      </div>
                                 </div>   
                             </div>           

                            <div class="w-100"></div>


                            <div class="row" id="permissions">
                            </div>


                          
                            <!-- <div class="w-100"></div>

                            <div class="row"> 
                             <div class="col-sm-4">
                              <div class="w-100"></div>
                                  <button type="submit" class="btn btn-primary">Save</button>
                            </div> 
                            </div> -->         
                        
                         </div>
                      </form>  
                  </div> 

                </div>
        </div>
                        </div>
                    </div>
<!-- sales lead window -->
                
            <!-- end page content -->
            <!-- start chat sidebar -->
           
       
        
    <form id="delete-form" action="" method="POST">
        {{ method_field('DELETE') }}  {{csrf_field()}}
        <input value="delete" style="display: none;" type="submit">
    </form>
  
 
<!-- end page content -->


@endsection



@section('scripts')

<script>

  $(document).ready(function(){
    /*if ($(this).is(':checked')) {
      $(this).closest('input.parent:checkbox').attr('checked', true);
    }*/
   
     $(document).on('click','.nested input[type=checkbox]',function () {
        $(this).parent().find(' input[type=checkbox]').prop('checked', $(this).is(':checked'));
          var sibs = false;
          $(this).closest('ul').children('li').each(function () {
            if($('input[type=checkbox]', this).is(':checked')) sibs=true;
          })
          $(this).parents('ul').prev().prop('checked', sibs);
      });

    $('#menuoption').change(function(){
         var menu_val = $(this).val();

         if(menu_val != ''){

          var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');


          $.ajax({
             type:"POST",
             url: "{{url('menuPermissions')}}/"+menu_val,  
             data: {_token: CSRF_TOKEN, role: '{{$role->id}}'},   
             success:function(res){ 
              $("#permissions").html(res);
                $('ul.nested li').each(function(){
                  var $childCheckboxes = $(this).find('input.inner-class'),
                      no_checked = $childCheckboxes.filter(':checked').length;


                  if($childCheckboxes.length == no_checked){
                    $(this).find('.sub-class').prop('checked',true);
                    $(this).find('.main-class').prop('checked',true);

                  }
                });
             }
          });



           

         }
    });



  });

</script>
 

@endsection
