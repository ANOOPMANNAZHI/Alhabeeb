<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;

use Modules\Masters\Entities\UnitType;
use Modules\Masters\Entities\PriceRange;
use Modules\Masters\Entities\Location;
use Modules\Masters\Entities\TenantType;
use Modules\Masters\Entities\BuildingType;
use Modules\Masters\Entities\EnquirySource;
use Modules\Sales\Entities\SalesEnquiry;
use Modules\Masters\Entities\Building;
use Modules\Masters\Entities\Unit;
use Modules\Masters\Entities\Work;

use Modules\Maintenance\Entities\PreferredTime;

use Modules\Maintenance\Entities\ComplaintEnquiry;
 

class AddEnquiryComposer
{     

    /**
     * Create a new profile composer.
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct( )
    {
       
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {

    $unitTypes = UnitType::active()->orderBy('id','ASC')->get();  
    $priceRanges = PriceRange::active()->orderBy('price_ranges_from', 'ASC')->get();  
    $locations = Location::active()->get();  
    $tenantTypes = TenantType::active()->get();  
    $buildingTypes = BuildingType::active()->get();  
    $enquirySources = EnquirySource::active()->get();  

    $salesTenLatest = SalesEnquiry::where('sales_type',1)->where('sales_enquiry_direct_contract',1)->latest()->first();
    $salesLanLatest = SalesEnquiry::where('sales_type',2)->where('sales_enquiry_direct_contract',1)->latest()->first();
    $complaintLatest = ComplaintEnquiry::latest()->first();
    $buildings = Building::active()->get();
    $units_s = Unit::active()->get(); 
    $works = Work::get(); 

    $preferred_time = PreferredTime::get(); 


    //dd($units);
	$prefix = prefixData('tenant_enquiry_no_prefix')->configuration_value;  
	
    if(!empty($salesTenLatest))
        $nextTenantCode = $prefix.str_pad($salesTenLatest->enquiry_index+1,4,'0',STR_PAD_LEFT);
    else
        $nextTenantCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);    

    $prefix = prefixData('landlord_enquiry_no_prefix')->configuration_value;

    if(!empty($salesLanLatest))
        $nextLandlordCode = $prefix.str_pad($salesLanLatest->enquiry_index+1,4,'0',STR_PAD_LEFT);
    else
        $nextLandlordCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);  

    $prefix = prefixData('complaint_prefix')->configuration_value;
    if(!empty($complaintLatest))
        $nextcomplaintCode = $prefix.str_pad($complaintLatest->id+1,4,'0',STR_PAD_LEFT);
    else
        $nextcomplaintCode = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);  

        $view->with(['unitTypes' => $unitTypes, 'locations' => $locations ,
                     'priceRanges' => $priceRanges, 'tenantTypes' => $tenantTypes , 
                     'buildingTypes' => $buildingTypes , 'enquirySources' => $enquirySources,
                     'nextTenantCode' => $nextTenantCode,'nextLandlordCode' => $nextLandlordCode,
                     'buildings'=>$buildings,'units_s'=>$units_s,'works'=>$works,
                      'nextcomplaintCode'=>$nextcomplaintCode,
                      'preferred_time' => $preferred_time

         ]);
    }
}
