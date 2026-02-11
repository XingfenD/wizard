<?php
/**
 * Wizard
 *
 * Original Code Copyright
 * @license     Apache2.0
 * @link        https://aicode.cc/
 * @copyright   管宜尧 <mylxsw@aicode.cc>
 *
 * Modified Code Copyright
 * @license     MPL2.0
 * @link        https://github.com/XingfenD
 * @copyright   Fendy <xingfen.fendy@outlook.com>
 *
 * Modifications:
 *  1. Use page external id instead of page id
 *     for every single route with arg 'page_id'
 *  2. Fix Issue#115 (original project) in Commit 59e746dd:
 *        Guests can see mind_mapping in read_only mode now without log_in
 */

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\BatchExportController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\MindMappingController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\OperationLogController;
use App\Http\Controllers\TagController;

Route::group(['middleware' => 'locale'], function () {
    // 如果启用 LDAP ，则不允许用户注册和重置密码
    $ldapDisabled = !ldap_enabled();
    $authRoutes = [
        'reset'    => $ldapDisabled,
        'verify'   => $ldapDisabled,
        'register' => $ldapDisabled && register_enabled(),
    ];
    Auth::routes($authRoutes);

    Route::group(['middleware' => 'global-auth'], function () {
        // 公共首页
        Route::get('/{catalog?}', [HomeController::class, 'home'])->name('home');
        // 项目公共页面
        Route::get('/project/{id}', [ProjectController::class, 'project'])->name('project:home');
        // 设置语言
        Route::get('/locale', [HomeController::class, 'lang'])->name('locale');

        // 分享页面
        Route::get('/s/{hash}', [ShareController::class, 'page'])->name('share:show');
        // 用户账号激活
        Route::get('/user/activate', [UserController::class, 'activate'])->name('user:activate');

        // 空白页，用于前端兼容
        Route::get('/blank', [HomeController::class, 'blank'])->name('blank');
        // 文档比较
        Route::post('/doc/compare', [CompareController::class, 'compare'])->name('doc:compare');
        // 阅读模式
        Route::get('/project/{id}/doc/{page_external_id}/read', [DocumentController::class, 'readMode'])->name('project:doc:read');

        // 小工具
        Route::group(['prefix' => 'tools', 'as' => 'tools:'], function () {
            Route::post('json-to-markdown', [ToolController::class, 'convertJsonToTable'])->name('json-to-markdown');
            Route::post('sql-to-markdown', [ToolController::class, 'convertSQLToMarkdownTable'])->name('sql-to-markdown');
            Route::post('sql-to-html', [ToolController::class, 'convertSQLToHTMLTable'])->name('sql-to-html');
        });

        // 文件导出
        Route::post('/export/{type}.pdf', [ExportController::class, 'pdf'])->name('export:pdf');
        Route::post('/export-file/{filename}', [ExportController::class, 'download'])->name('export:download');

        // 批量导出（项目）
        Route::post('/project/{project_id}/export', [BatchExportController::class, 'batchExport'])->name('export:batch');

        Route::group(['prefix' => 'project', 'middleware' => 'share', 'as' => 'project:'], function () {
            // 项目分享
            Route::get('/{id}/doc/{page_external_id}.json', [DocumentController::class, 'getPageJSON'])->name('doc:json');
            Route::get('/{id}/doc/{page_external_id}/histories/{history_id}.json',
                [HistoryController::class, 'getPageJSON'])->name('doc:history:json');
        });

        Route::group(['prefix' => 'swagger', 'as' => 'swagger:'], function () {
            // 获取swagger文档内容
            Route::get('/{id}/doc/{page_external_id}.yml', [DocumentController::class, 'getSwagger'])->name('doc:yml');
            Route::get('/{id}/doc/{page_external_id}.json', [DocumentController::class, 'getJson'])->name('doc:json');
        });

        // 用户扮演
        Route::group(['prefix' => 'impersonate', 'as' => 'impersonate:'], function () {
            Route::post('/{id}', [ImpersonateController::class, 'impersonate'])->name('start');
            Route::delete('/', [ImpersonateController::class, 'stopImpersonate'])->name('stop');
        });

        // 系统管理
        Route::group(['middleware' => ['auth', 'auth.admin'], 'prefix' => 'admin', 'as' => 'admin:'],
            function () {
                // 仪表盘
                Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

                // 用户组管理
                Route::get('/groups', [GroupController::class, 'groups'])->name('groups');
                Route::post('/groups', [GroupController::class, 'add'])->name('groups:add');
                Route::delete('/groups/{id}', [GroupController::class, 'delete'])->name('groups:del');
                Route::get('/groups/{id}', [GroupController::class, 'info'])->name('groups:view');
                Route::post('/groups/{id}/user', [GroupController::class, 'addUser'])->name('groups:users:add');
                Route::post('/groups/{id}', [GroupController::class, 'update'])->name('groups:update');
                Route::delete('/groups/{id}/users/{user_id}', [GroupController::class, 'removeUser'])
                     ->name('groups:users:del');
                Route::post('/groups/{id}/projects', [GroupController::class, 'grantProjects'])
                     ->name('groups:projects:add');

                // 用户管理
                Route::get('/users', [UserController::class, 'users'])->name('users');
                Route::get('/users/{id}', [UserController::class, 'user'])->name('user');
                Route::post('/users/{id}', [UserController::class, 'updateUser'])->name('user:update');
                Route::post('/users/{id}/groups', [UserController::class, 'joinGroup'])->name('user:join-group');

                // 项目目录管理
                Route::get('/catalogs', [CatalogController::class, 'catalogs'])->name('catalogs');
                Route::post('/catalogs', [CatalogController::class, 'add'])->name('catalogs:add');
                Route::get('/catalogs/{id}', [CatalogController::class, 'info'])->name('catalogs:view');
                Route::post('/catalogs/{id}', [CatalogController::class, 'edit'])->name('catalogs:edit');
                Route::delete('/catalogs/{id}', [CatalogController::class, 'delete'])->name('catalogs:delete');
                Route::delete('/catalogs/{id}/project/{project_id}', [CatalogController::class, 'removeProject'])
                     ->name('catalogs:project:del');
            });

        // 文档搜索
        Route::group(['prefix' => 'search', 'as' => 'search:'], function () {
            Route::get('/', [SearchController::class, 'search'])->name('search');
        });

        // 思维导图编辑器（允许未登录用户访问，只读模式）
        Route::get('/mind-mapping/editor', [MindMappingController::class, 'editor'])->name('mind-mapping:editor');

        Route::group(['middleware' => 'auth'], function () {
            // 个人首页
            Route::get('/home', [ProjectController::class, 'home'])->name('user:home');
            // 文件上传
            Route::post('/upload', [FileController::class, 'imageUpload'])->name('upload');

            // 思维导图保存
            Route::group(['prefix' => 'mind-mapping', 'as' => 'mind-mapping:'], function () {
                Route::post('/', [MindMappingController::class, 'save'])->name('save');
            });

            // 用户信息
            Route::group(['prefix' => 'user', 'as' => 'user:'], function () {
                // 重新发送账号激活邮件
                Route::post('/activate/email', [UserController::class, 'sendActivateEmail'])
                     ->name('activate:send');
                // 基本信息
                Route::get('/', [UserController::class, 'basic'])->name('basic');
                Route::post('/', [UserController::class, 'basicHandle'])->name('basic:handle');

                // 修改密码
                Route::get('/password', [UserController::class, 'password'])->name('password');
                Route::post('/password', [UserController::class, 'passwordHandle'])->name('password:handle');

                // 通知消息
                Route::get('/notifications', [NotificationController::class, 'lists'])->name('notifications');
                Route::put('/notifications/all', [NotificationController::class, 'readAll'])
                     ->name('notifications:read-all');
                Route::put('/notifications/{notification_id}', [NotificationController::class, 'read'])
                     ->name('notifications:read');

                // 个人模板管理
                Route::get('/templates', [TemplateController::class, 'all'])->name('templates');
                Route::delete('/templates/{id}', [TemplateController::class, 'deleteTemplate'])
                     ->name('templates:delete');
                Route::get('/templates/{id}', [TemplateController::class, 'edit'])->name('templates:edit');
                Route::put('/templates/{id}', [TemplateController::class, 'editHandle'])
                     ->name('templates:edit:handle');

                // 用户可写的项目列表
                Route::get('/writable-projects', [UserController::class, 'projectsCanWrite']);
            });

            Route::group(['prefix' => 'project', 'as' => 'project:'], function () {
                // 创建新项目
                Route::post('/', [ProjectController::class, 'newProjectHandle'])->name('new:handle');
                Route::delete('/{id}', [ProjectController::class, 'delete'])->name('delete');

                // 项目配置
                Route::get('/{id}/setting', [ProjectController::class, 'setting'])->name('setting:show');
                Route::post('/{id}/setting', [ProjectController::class, 'settingHandle'])->name('setting:handle');
                // 回收项目权限
                Route::delete('/{id}/privilege/{group_id}', [ProjectController::class, 'groupPrivilegeRevoke'])
                     ->name('privilege:revoke');

                // 创建新的文档
                Route::get('/{id}/doc', [DocumentController::class, 'newPage'])->name('doc:new:show');
                Route::post('/{id}/doc', [DocumentController::class, 'newPageHandle'])->name('doc:new:handle');
                Route::post('/{id}/doc-import', [ImportController::class, 'documents'])->name('doc:import');

                // 编辑文档
                Route::get('/{id}/doc/{page_external_id}', [DocumentController::class, 'editPage'])->name('doc:edit:show');
                Route::post('/{id}/doc/{page_external_id}', [DocumentController::class, 'editPageHandle'])
                     ->name('doc:edit:handle');
                Route::delete('/{id}/doc/{page_external_id}', [DocumentController::class, 'deletePage'])
                     ->name('doc:delete');

                // 文档同步
                Route::post('/{id}/doc/{page_external_id}/sync-from', [DocumentController::class, 'syncFromRemote'])
                     ->name('doc:sync-from');

                // 文档标记
                Route::put('/{id}/doc/{page_external_id}/mark-status', [DocumentController::class, 'markStatus'])->name('doc:mark-status');

                // 文档分享
                Route::post('/{id}/doc/{page_external_id}/share', [ShareController::class, 'create'])->name('doc:share');
                Route::delete('/{id}/doc/{page_external_id}/share', [ShareController::class, 'delete'])->name('doc:share:delete');

                // 文档评论
                Route::post('/{id}/doc/{page_external_id}/comments', [CommentController::class, 'publish'])
                     ->name('doc:comment');

                // 文档附件
                Route::get('/{id}/doc/{page_external_id}/attachments', [AttachmentController::class, 'page'])
                     ->name('doc:attachment');
                Route::delete('/{id}/doc/{page_external_id}/attachments/{attachment_id}',
                    [AttachmentController::class, 'delete'])->name('doc:attachment:delete');
                Route::post('/{id}/doc/{page_external_id}/attachments', [AttachmentController::class, 'upload'])
                     ->name('doc:attachment:upload');

                // ajax获取文档是否过期
                Route::get('/{id}/doc/{page_external_id}/expired', [DocumentController::class, 'checkPageExpired'])->name('doc:expired');
                // 文档评价
                Route::post('/{id}/doc/{page_external_id}/score', [DocumentController::class, 'updateDocumentScore'])->name('doc:score');

                // 文档历史记录
                Route::get('/{id}/doc/{page_external_id}/histories', [HistoryController::class, 'pages'])
                     ->name('doc:history');
                Route::get('/{id}/doc/{page_external_id}/histories/{history_id}', [HistoryController::class, 'page'])
                     ->name('doc:history:show');
                Route::put('/{id}/doc/{page_external_id}/histories/{history_id}', [HistoryController::class, 'recover'])
                     ->name('doc:history:recover');

                // 关注项目
                Route::post('/{id}/favorite', [ProjectController::class, 'favorite'])->name('favorite');

                // 跨项目移动文档
                Route::post('/{project_id}/doc/{page_external_id}/move-to', [DocumentController::class, 'move'])->name('move');
                Route::get('/{project_id}/doc-selector', [ProjectController::class, 'documentSelector'])->name('doc-selector');
            });

            // 创建模板
            Route::post('/template', [TemplateController::class, 'create'])->name('template:create');

            // ajax获取操作历史
            Route::get('/operations/recently', [OperationLogController::class, 'recently'])
                 ->name('operation-log:recently');
            Route::group(['prefix' => 'tag', 'as' => 'tag:'], function () {
                // 创建标签
                Route::post('/', [TagController::class, 'store'])->name('tag:store');
            });
        });
    });
});