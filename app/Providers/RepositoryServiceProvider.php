<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\CertificateRepository;
use App\Contracts\Repositories\CourseAccessGrantRepository;
use App\Contracts\Repositories\CourseAssignmentRepository;
use App\Contracts\Repositories\CourseCategoryRepository;
use App\Contracts\Repositories\CourseRepository;
use App\Contracts\Repositories\CustomDomainRepository;
use App\Contracts\Repositories\ImpersonationRepository;
use App\Contracts\Repositories\MediaRepository;
use App\Contracts\Repositories\PlatformDashboardRepository;
use App\Contracts\Repositories\QuestionRepository;
use App\Contracts\Repositories\ResellerKlantCourseGrantRepository;
use App\Contracts\Repositories\ResellerKlantRepository;
use App\Contracts\Repositories\ResellerMailTemplateRepository;
use App\Contracts\Repositories\ResellerRepository;
use App\Contracts\Repositories\ResellerThemeRepository;
use App\Contracts\Repositories\UserInviteRepository;
use App\Repositories\Eloquent\EloquentCertificateRepository;
use App\Repositories\Eloquent\EloquentCourseAccessGrantRepository;
use App\Repositories\Eloquent\EloquentCourseAssignmentRepository;
use App\Repositories\Eloquent\EloquentCourseCategoryRepository;
use App\Repositories\Eloquent\EloquentCourseRepository;
use App\Repositories\Eloquent\EloquentCustomDomainRepository;
use App\Repositories\Eloquent\EloquentImpersonationRepository;
use App\Repositories\Eloquent\EloquentMediaRepository;
use App\Repositories\Eloquent\EloquentPlatformDashboardRepository;
use App\Repositories\Eloquent\EloquentQuestionRepository;
use App\Repositories\Eloquent\EloquentResellerKlantCourseGrantRepository;
use App\Repositories\Eloquent\EloquentResellerKlantRepository;
use App\Repositories\Eloquent\EloquentResellerMailTemplateRepository;
use App\Repositories\Eloquent\EloquentResellerRepository;
use App\Repositories\Eloquent\EloquentResellerThemeRepository;
use App\Repositories\Eloquent\EloquentUserInviteRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds each repository interface in App\Contracts\Repositories to its
 * Eloquent implementation in App\Repositories\Eloquent. Add one
 * $this->app->bind() call per repository -- see CLAUDE.md §3a for when a
 * repository is warranted.
 */
final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ResellerRepository::class, EloquentResellerRepository::class);
        $this->app->bind(CustomDomainRepository::class, EloquentCustomDomainRepository::class);
        $this->app->bind(ResellerKlantRepository::class, EloquentResellerKlantRepository::class);
        $this->app->bind(UserInviteRepository::class, EloquentUserInviteRepository::class);
        $this->app->bind(ImpersonationRepository::class, EloquentImpersonationRepository::class);
        $this->app->bind(ResellerThemeRepository::class, EloquentResellerThemeRepository::class);
        $this->app->bind(ResellerMailTemplateRepository::class, EloquentResellerMailTemplateRepository::class);
        $this->app->bind(CourseCategoryRepository::class, EloquentCourseCategoryRepository::class);
        $this->app->bind(CourseRepository::class, EloquentCourseRepository::class);
        $this->app->bind(MediaRepository::class, EloquentMediaRepository::class);
        $this->app->bind(QuestionRepository::class, EloquentQuestionRepository::class);
        $this->app->bind(CertificateRepository::class, EloquentCertificateRepository::class);
        $this->app->bind(CourseAssignmentRepository::class, EloquentCourseAssignmentRepository::class);
        $this->app->bind(CourseAccessGrantRepository::class, EloquentCourseAccessGrantRepository::class);
        $this->app->bind(ResellerKlantCourseGrantRepository::class, EloquentResellerKlantCourseGrantRepository::class);
        $this->app->bind(PlatformDashboardRepository::class, EloquentPlatformDashboardRepository::class);
    }
}
