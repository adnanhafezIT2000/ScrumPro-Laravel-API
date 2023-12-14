<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionRoleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DefinitionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StoriesController;
use App\Http\Controllers\AcceptanceCritiriaController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DepedenceController;
use App\Http\Controllers\DashboardController;


Route::group( ['prefix' => 'ScrumPro'] , function(){

    /***************
     * Dashboard
    ***************/
    Route::get('get-admin-dashboard' , [DashboardController::class , 'getAdminDashboard']);
    Route::get('get-dashboard/{roleName}/{projectID}/{developerID}' , [DashboardController::class , 'getDashboard']);

    /*****************
     * Permissions
    *****************/
    Route::apiResource('permissions' , PermissionController::class);

    /***********
     * Roles
    ***********/
    Route::apiResource('roles' , RoleController::class);

    /**********************
     * Permissions-Roles
    **********************/
    Route::apiResource('permission-role' , PermissionRoleController::class);
    Route::post('manage-assign-permission-to-role' , [PermissionRoleController::class , 'manageAssignPermissionToRole']);

    /****************
     * Categories
    ****************/
    Route::apiResource('categories' , CategoryController::class);

    /************
     * Skills
    ************/
    Route::apiResource('skills' , SkillController::class);
    Route::get('skills/show-skill/{skill}' , [SkillController::class , 'showSkill']);

    /***********
     * Users
    ***********/
    Route::apiResource('users' , UserController::class);
    Route::post('change-user-status' , [UserController::class , 'changeUserStatus']);

    /*****************
     * Teams
    *****************/
    Route::apiResource('teams' , TeamController::class);
    Route::get('get-masters-and-developers' , [TeamController::class , 'getMastersAndDevelopers']);
    Route::get('get-teams-to-create-project' , [TeamController::class , 'getTeamsToCreateProject']);

    /***********
     * Login
    ***********/
    Route::post('admin-login' , [LoginController::class , 'adminLogin']);
    Route::post('members-login' , [LoginController::class , 'memebersLogin']);

    /**************
     * Password
    **************/
    Route::post('change-password' , [PasswordController::class , 'changePassword']);

    /**********************
     * Update User Image
    **********************/
    Route::post('update-user-image' , [ImageController::class , 'updateUserImage']);

    /*************
     * Projects
    *************/
    Route::apiResource('projects' , ProjectController::class);
    Route::get('get-all-projects-owner/{ownerID}' , [ProjectController::class , 'getAllProjectsOwner']);
    Route::get('get-all-projects-master/{masterID}' , [ProjectController::class , 'getAllProjectsMaster']);
    Route::get('get-owners' , [UserController::class , 'getOwners']);
    Route::get('get-all-projects-developer/{developerID}' , [ProjectController::class , 'getAllProjectsDeveloper']);
    Route::get('get-all-projects-client/{clientID}' , [ProjectController::class , 'getAllProjectsClient']);
    Route::get('close-project/{projectID}' , [ProjectController::class , 'closeProject']);

    /************************
     * Definitions Of Done
    ************************/
    Route::apiResource('definitions-of-done' , DefinitionController::class);
    Route::get('get-definition-for-project/{projectID}' , [DefinitionController::class , 'getDefinitionForProject']);
    Route::get('show-definition/{id}' , [DefinitionController::class , 'showDefinition']);
    Route::delete('destroy-definition/{id}' , [DefinitionController::class , 'destroyDefinition']);
    Route::put('update-definition/{id}', [DefinitionController::class , 'updateDefinition']);

    /*********************
     * Backlog + Stories
    *********************/
    Route::get('backlog/{projectID}' , [StoriesController::class , 'backlog']);
    Route::apiResource('stories' , StoriesController::class);
    Route::put('update-story-point/{id}' , [StoriesController::class , 'updateStoryPoint']);
    Route::put('update-story-title/{id}' , [StoriesController::class , 'updateStoryTitle']);
    Route::put('update-story-description/{id}' , [StoriesController::class , 'updateStoryDescription']);
    Route::put('update-story-priority/{id}' , [StoriesController::class , 'updateStoryPriority']);
    Route::get('get-stories-board/{projectID}' , [StoriesController::class , 'getStoriesBoard']);
    Route::post('start-story-work' , [StoriesController::class , 'startStoryWork']);

    /************************
     * Acceptance Critiria
    ************************/
    Route::apiResource('acceptance-critiria' , AcceptanceCritiriaController::class);
    Route::get('get-acceptance-critiria-for-story/{id}' , [AcceptanceCritiriaController::class , 'getAcceptanceCritiriaForStory']);

    /* Comments */
    Route::apiResource('comments' , CommentController::class);
    Route::get('get-comments-for-story/{story}' ,[CommentController::class,'getCommentsForStory']);
    Route::get('get-comments-for-task/{task}' , [CommentController::class , 'getCommentsForTask']);

    /* Sprints */
    Route::apiResource('sprints' , SprintController::class);
    Route::get('get-backlog-for-sprints/{projectID}' , [SprintController::class , 'getBacklogForSprints']);
    Route::get('get-sprints-for-project/{projectID}' ,[SprintController::class,'getSprintsForProject']);
    Route::post('add-story-to-sprint' , [SprintController::class , 'addStoryToSprint']);
    Route::post('remove-story-from-sprint' , [SprintController::class , 'removeStoryFromSprint']);
    Route::post('update-story-rank' , [SprintController::class , 'updateStoryRank']);
    Route::get('check-create-sprint/{projectID}' , [SprintController::class , 'checkCreateSprint']);
    Route::get('start-sprint/{sprintID}' , [SprintController::class , 'startSprint']);

    /* Meetings */
    Route::apiResource('meeting' , MeetingController::class);
    Route::get('get-all-planning-meeting/{projectID}' , [MeetingController::class , 'getAllSprintPlanningMeeting']);
    Route::post('store-planning-meeting' , [MeetingController::class , 'storePlanningMeeting']);
    Route::post('store-daily-scrum-meeting' , [MeetingController::class , 'storeDailyScrumMeeting']);
    Route::post('update-daily-scrum-meeting' , [MeetingController::class , 'updateDailyScrumMeeting']);

    /* Tasks */
    Route::apiResource('tasks' , TaskController::class);
    Route::get('get-tasks-for-story/{storyID}' ,[TaskController::class,'getTasksForStory']);
    Route::put('update-task-title/{id}' , [TaskController::class , 'updateTaskTitle']);
    Route::put('update-task-description/{id}' , [TaskController::class , 'updateTaskDescription']);
    Route::get('get-tasks-box/{projectID}' , [TaskController::class , 'getTasksBox']);
    Route::post('select-user-tasks' , [TaskController::class , 'selectUserTasks']);
    Route::post('finished-task' , [TaskController::class , 'finishedTask']);

    /* Dependences */
    Route::apiResource('dependences' , DepedenceController::class);
});

