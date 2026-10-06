<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Building Inspection & Maintenance: areas + checklist masters, building -> executive assignment,
 * visits, checklist results, issues (with action plan), issue timeline and photos.
 * Issue categories are the complaint module's Works; Cleaning and Other are added when missing.
 * New tables only - no existing data changes apart from the two Works rows.
 */
class CreateBuildingInspectionTables extends Migration
{
    const AREAS = [
        'Common Areas'        => ['Corridors and stairways', 'Handrails and floor finishes'],
        'Lifts'               => ['Lift operation and levelling', 'Emergency intercom'],
        'Water Tanks & Pumps' => ['Tank cleanliness and covers', 'Pump operation and leakage'],
        'Electrical Rooms'    => ['Panels and exposed wiring', 'Room access and ventilation'],
        'Fire Safety'         => ['Extinguishers and inspection tags', 'Fire exits and alarm panel'],
        'Parking'             => ['Lighting and surface condition', 'Access and parking markings'],
        'Roof'                => ['Waterproofing and ponding', 'Roof access and safety'],
        'Façade'              => ['Cladding and external cracks', 'Loose fixtures and signage'],
        'Drainage'            => ['Drain flow and odour', 'Manholes and covers'],
        'Security'            => ['CCTV and access control', 'Guard log and visitor register'],
        'Cleanliness'         => ['Common area cleaning', 'Waste collection and bins'],
    ];
    const NEW_WORKS = ['Cleaning', 'Other'];

    public function up()
    {
        Schema::create('inspection_areas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->integer('sort_order')->default(0);
            $table->smallInteger('status')->default(1);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('inspection_checklist_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('area_id')->index();
            $table->string('name', 200);
            $table->integer('sort_order')->default(0);
            $table->smallInteger('status')->default(1);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        // one current executive per building (assigned_till null); older rows are the history
        Schema::create('building_executives', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('building_id');
            $table->integer('user_id')->index();
            $table->date('assigned_from');
            $table->date('assigned_till')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->index(['building_id', 'assigned_till']);
        });
        DB::statement('CREATE UNIQUE INDEX building_executives_current_unique ON building_executives (building_id) WHERE assigned_till IS NULL');

        // visit_type 1 Monthly inspection, 2 Follow-up, 3 Additional inspection
        // status 0 Scheduled, 2 Completed, 9 Cancelled
        Schema::create('inspection_visits', function (Blueprint $table) {
            $table->increments('id');
            $table->string('visit_no', 20)->nullable()->unique();
            $table->integer('building_id')->index();
            $table->integer('executive_id')->index();
            $table->smallInteger('visit_type');
            $table->date('visit_month');
            $table->date('planned_date');
            $table->text('purpose')->nullable();
            $table->smallInteger('status')->default(0);
            $table->text('cancel_reason')->nullable();
            $table->integer('cancelled_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('general_remarks')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->index(['visit_month', 'status']);
        });
        DB::statement('CREATE UNIQUE INDEX inspection_visits_monthly_unique ON inspection_visits (building_id, visit_month) WHERE visit_type = 1 AND status <> 9');

        // result 1 OK, 2 Issue, 3 N/A
        Schema::create('inspection_visit_results', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('visit_id')->index();
            $table->integer('area_id');
            $table->integer('checklist_item_id');
            $table->smallInteger('result');
            $table->integer('issue_id')->nullable();
            $table->timestamps();
        });

        // priority 1 Critical, 2 High, 3 Medium, 4 Low; status 1 Open, 2 In Progress, 3 Resolved, 4 Closed (verified)
        // source 1 Inspection, 2 Manual
        Schema::create('inspection_issues', function (Blueprint $table) {
            $table->increments('id');
            $table->string('issue_no', 20)->nullable()->unique();
            $table->integer('building_id')->index();
            $table->integer('unit_id')->nullable();
            $table->integer('area_id')->nullable();
            $table->integer('checklist_item_id')->nullable();
            $table->integer('work_id');
            $table->smallInteger('priority');
            $table->smallInteger('status')->default(1);
            $table->smallInteger('source');
            $table->integer('visit_id')->nullable()->index();
            $table->integer('executive_id')->index();
            $table->integer('raised_by');
            $table->timestamp('raised_at');
            $table->date('target_date');
            $table->text('description');
            $table->text('action_plan')->nullable();
            $table->string('responsible_party', 50)->nullable();
            $table->decimal('estimated_cost', 12, 3)->nullable();
            $table->boolean('landlord_approval')->nullable();
            $table->integer('complaint_enquiry_id')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->integer('resolved_by')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->integer('closed_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->index(['status', 'priority']);
        });

        // kind: raised, progress, plan, followup, resolved, verified, sent_back, complaint
        // condition (follow-up during a visit): 1 Still open, 2 Resolved, 3 Worsened
        Schema::create('inspection_issue_updates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('issue_id')->index();
            $table->integer('visit_id')->nullable();
            $table->string('kind', 20);
            $table->smallInteger('condition')->nullable();
            $table->text('note')->nullable();
            $table->integer('created_by');
            $table->timestamps();
        });

        // kind 1 Before, 2 Progress, 3 After
        Schema::create('inspection_photos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('issue_id')->index();
            $table->integer('update_id')->nullable();
            $table->smallInteger('kind');
            $table->string('path');
            $table->string('thumb_path');
            $table->integer('created_by');
            $table->timestamps();
        });

        $now = now();
        $order = 0;
        foreach (self::AREAS as $area => $items) {
            $areaId = DB::table('inspection_areas')->insertGetId(['name' => $area, 'sort_order' => ++$order, 'status' => 1,
                'created_by' => 1, 'created_at' => $now, 'updated_at' => $now]);
            foreach ($items as $k => $item) {
                DB::table('inspection_checklist_items')->insert(['area_id' => $areaId, 'name' => $item, 'sort_order' => $k + 1,
                    'status' => 1, 'created_by' => 1, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        // Works = issue categories; account code as General Works
        $general = DB::table('works')->whereRaw('lower(works_code) = ?', ['general works'])->value('acc_code_id');
        foreach (self::NEW_WORKS as $work) {
            if (!DB::table('works')->whereRaw('lower(works_code) = ?', [strtolower($work)])->exists()) {
                DB::table('works')->insert(['works_code' => $work, 'works_status' => 1, 'works_type' => 1, 'acc_code_id' => $general,
                    'created_by' => 1, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    public function down()
    {
        foreach (['inspection_photos', 'inspection_issue_updates', 'inspection_issues', 'inspection_visit_results', 'inspection_visits',
            'building_executives', 'inspection_checklist_items', 'inspection_areas'] as $t) {
            Schema::dropIfExists($t);
        }
        // the Works rows stay: complaints may already use them
    }
}
