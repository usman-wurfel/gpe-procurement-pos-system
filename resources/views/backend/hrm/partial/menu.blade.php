<?php
    $department_active     = $role_has_permissions_list->where('name', 'department')->first()
                            || $role_has_permissions_list->where('name', 'department-view')->first()
                            || $role_has_permissions_list->where('name', 'department-add')->first()
                            || $role_has_permissions_list->where('name', 'department-manage')->first();
    $designations_active   = $role_has_permissions_list->where('name', 'designations')->first()
                            || $role_has_permissions_list->where('name', 'designations-view')->first()
                            || $role_has_permissions_list->where('name', 'designations-add')->first()
                            || $role_has_permissions_list->where('name', 'designations-manage')->first();
    $shift_active          = $role_has_permissions_list->where('name', 'shift')->first()
                            || $role_has_permissions_list->where('name', 'shift-view')->first()
                            || $role_has_permissions_list->where('name', 'shift-add')->first()
                            || $role_has_permissions_list->where('name', 'shift-manage')->first();
    $overtime_active       = $role_has_permissions_list->where('name', 'overtime')->first()
                            || $role_has_permissions_list->where('name', 'overtime-view')->first()
                            || $role_has_permissions_list->where('name', 'overtime-add')->first()
                            || $role_has_permissions_list->where('name', 'overtime-manage')->first();
    $leave_type_active     = $role_has_permissions_list->where('name', 'leave-type')->first()
                            || $role_has_permissions_list->where('name', 'leave-type-view')->first()
                            || $role_has_permissions_list->where('name', 'leave-type-add')->first()
                            || $role_has_permissions_list->where('name', 'leave-type-manage')->first();
    $leave_active          = $role_has_permissions_list->where('name', 'leave')->first()
                            || $role_has_permissions_list->where('name', 'leave-view')->first()
                            || $role_has_permissions_list->where('name', 'leave-add')->first()
                            || $role_has_permissions_list->where('name', 'leave-manage')->first();
    $hrm_panel_active      = $role_has_permissions_list->where('name', 'hrm-panel')->first();
    $sale_agent_active     = $role_has_permissions_list->where('name', 'sale-agents')->first()
                            || $role_has_permissions_list->where('name', 'sale-agents-view')->first()
                            || $role_has_permissions_list->where('name', 'sale-agents-add')->first()
                            || $role_has_permissions_list->where('name', 'sale-agents-manage')->first();

    $index_employee_active = $role_has_permissions_list->where('name', 'employees-index')->first();
    $attendance_active     = $role_has_permissions_list->where('name', 'attendance')->first()
                            || $role_has_permissions_list->where('name', 'attendance-view')->first()
                            || $role_has_permissions_list->where('name', 'attendance-add')->first()
                            || $role_has_permissions_list->where('name', 'attendance-manage')->first();
    $payroll_active        = $role_has_permissions_list->where('name', 'payroll')->first()
                            || $role_has_permissions_list->where('name', 'payroll-view')->first()
                            || $role_has_permissions_list->where('name', 'payroll-add')->first()
                            || $role_has_permissions_list->where('name', 'payroll-manage')->first();
    $holiday_active        = $role_has_permissions_list->where('name', 'holiday')->first()
                            || $role_has_permissions_list->where('name', 'holiday-view')->first()
                            || $role_has_permissions_list->where('name', 'holiday-add')->first()
                            || $role_has_permissions_list->where('name', 'holiday-manage')->first();
?>

<section class="no-print mb-4">
    <nav class="navbar navbar-expand-lg navbar-light bg-white border rounded shadow-sm mt-3 mx-3">
        <div class="container-fluid" style="background: #fffcf2">

            <a class="navbar-brand font-weight-bold text-primary" href="{{ route('hrm-panel') }}">
                <i class="dripicons-wallet mr-2"></i> {{ __("db.HRM") }}
            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#hrmNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="hrmNavbar">
                <ul class="navbar-nav">

                    @if($index_employee_active)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('employees.index') ? 'active' : '' }}"
                           href="{{ route('employees.index') }}">
                            <i class="dripicons-clock mr-1"></i> {{ __("db.Employees") }}
                        </a>
                    </li>
                    @endif
                    @if($payroll_active)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('payroll.index') ? 'active' : '' }}"
                           href="{{ route('payroll.index') }}">
                            <i class="dripicons-wallet mr-1"></i> {{ __("db.Payroll") }}
                        </a>
                    </li>
                    @endif

                    @if($attendance_active)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('attendance.index') ? 'active' : '' }}"
                           href="{{ route('attendance.index') }}">
                            <i class="dripicons-time-reverse mr-1"></i> {{ __("db.Attendance") }}
                        </a>
                    </li>
                    @endif

                    @if($leave_type_active)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('leave-type.index') ? 'active' : '' }}"
                           href="{{ route('leave-type.index') }}">
                            <i class="dripicons-briefcase mr-1"></i> {{ __("db.Leave Type") }}
                        </a>
                    </li>
                    @endif

                    @if($leave_active)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('leave.index') ? 'active' : '' }}"
                           href="{{ route('leave.index') }}">
                            <i class="dripicons-to-do mr-1"></i> {{ __("db.Leaves") }}
                        </a>
                    </li>
                    @endif

                    @if($holiday_active)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('holidays.index') ? 'active' : '' }}"
                           href="{{ route('holidays.index') }}">
                            <i class="dripicons-calendar mr-1"></i> {{ __("db.Holiday") }}
                        </a>
                    </li>
                    @endif

                    @if($shift_active)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('shift.index') ? 'active' : '' }}"
                           href="{{ route('shift.index') }}">
                            <i class="dripicons-calendar mr-1"></i> {{ __("db.Shift") }}
                        </a>
                    </li>
                    @endif

                    @if($overtime_active)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('overtime.index') ? 'active' : '' }}"
                           href="{{ route('overtime.index') }}">
                            <i class="dripicons-briefcase mr-1"></i> {{ __("db.Overtime") }}
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
</section>

<style>
    section {
    padding: 0px !important;
}
.navbar-nav .nav-link {
    color: #444;
    font-weight: 500;
    transition: all 0.3s ease;
    border-radius: 5px;
}
.navbar-nav .nav-link:hover {
    background-color: #f5f7ff;
    color: #007bff;
}
.navbar-nav .nav-link.active {
    color: #0798bd !important;
    font-weight: 700;
}
</style>
