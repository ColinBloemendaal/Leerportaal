<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Courses\AssignCourseToCursist;
use App\Actions\Courses\AssignCourseToGroup;
use App\Actions\Courses\BulkAssignCourseToCursists;
use App\Contracts\Repositories\CourseRepository;
use App\Contracts\Repositories\GroupRepository;
use App\Contracts\Repositories\UserRepository;
use App\Http\Requests\Courses\BulkStoreCourseAssignmentRequest;
use App\Http\Requests\Courses\GroupStoreCourseAssignmentRequest;
use App\Http\Requests\Courses\StoreCourseAssignmentRequest;
use App\Http\Resources\CourseOptionResource;
use App\Http\Resources\CursistOptionResource;
use App\Http\Resources\GroupOptionResource;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * TODO.md Phase 5 "Assignment UI": the backend (AssignCourseToCursist,
 * BulkAssignCourseToCursists, AssignCourseToGroup) has existed since
 * earlier phases with no consuming page -- this is that page, now that
 * Phase 7's admin panel exists for it to live in.
 */
final class CourseAssignmentController extends Controller
{
    public function create(CourseRepository $courses, UserRepository $users, GroupRepository $groups, TenantContext $tenantContext): Response
    {
        // Same access boundary as the existing courses/assignments index
        // controllers.
        $this->authorize('viewAny', 'App\Models\ResellerKlant');

        $resellerId = $tenantContext->id();
        abort_if($resellerId === null, 404);

        return Inertia::render('Admin/Reseller/Assignments/Create', [
            'courses' => CourseOptionResource::collection($courses->visibleToCurrentReseller()),
            'cursisten' => CursistOptionResource::collection($users->cursistenForReseller($resellerId)),
            'groups' => GroupOptionResource::collection($groups->forCurrentReseller()),
        ]);
    }

    public function store(StoreCourseAssignmentRequest $request, CourseRepository $courses, AssignCourseToCursist $assign): RedirectResponse
    {
        $course = $courses->findById($request->courseId());
        abort_if($course === null, 404);

        $assign($course, $request->toDto());

        return to_route('admin.reseller.assignments.index')->with('success', __('Course assigned.'));
    }

    public function storeBulk(BulkStoreCourseAssignmentRequest $request, CourseRepository $courses, BulkAssignCourseToCursists $bulkAssign): RedirectResponse
    {
        $course = $courses->findById($request->courseId());
        abort_if($course === null, 404);

        $dto = $request->toDto();
        $bulkAssign($course, $dto->userIds, $dto->assignedByUserId);

        return to_route('admin.reseller.assignments.index')
            ->with('success', __(':count course assignment(s) created.', ['count' => count($dto->userIds)]));
    }

    public function storeGroup(GroupStoreCourseAssignmentRequest $request, CourseRepository $courses, GroupRepository $groups, AssignCourseToGroup $assignToGroup): RedirectResponse
    {
        $course = $courses->findById($request->courseId());
        $group = $groups->findForCurrentReseller($request->groupId());
        abort_if($course === null || $group === null, 404);

        $dto = $request->toDto();
        $assignToGroup($course, $group, $dto->assignedByUserId);

        return to_route('admin.reseller.assignments.index')->with('success', __('Course assigned to the group.'));
    }
}
