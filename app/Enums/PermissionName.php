<?php

namespace App\Enums;

enum PermissionName: string
{
    case PlatformAccess = 'platform.access';
    case AccountsManage = 'accounts.manage';
    case RegistrationsManage = 'registrations.manage';
    case OrganizationManage = 'organization.manage';
    case StaffApplicationsReview = 'staff_applications.review';
    case WorkerApplicationsReview = 'worker_applications.review';
    case CatalogManage = 'catalog.manage';
    case RoutingManage = 'routing.manage';
    case ReportsManageAll = 'reports.manage.all';
    case AuditView = 'audit.view';
    case SlaManage = 'sla.manage';
    case ReportsViewDepartment = 'reports.view.department';
    case ReportsTriageDepartment = 'reports.triage.department';
    case WorkOrdersManageDepartment = 'work_orders.manage.department';
    case AssignmentsManageDepartment = 'assignments.manage.department';
    case VerificationManageDepartment = 'verification.manage.department';
    case FeedbackRateWorkers = 'feedback.rate_workers';
    case AnalyticsViewDepartment = 'analytics.view.department';
    case AssignmentsViewOwn = 'assignments.view.own';
    case AssignmentsUpdateOwn = 'assignments.update.own';
    case AssignmentsEvidenceCreateOwn = 'assignments.evidence.create.own';
    case ReportsCreate = 'reports.create';
    case ReportsViewOwn = 'reports.view.own';
    case ReportsCommentOwn = 'reports.comment.own';
    case FeedbackCreateOwn = 'feedback.create.own';
}
