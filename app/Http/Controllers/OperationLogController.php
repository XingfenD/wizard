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
 */
namespace App\Http\Controllers;


use App\Repositories\Catalog;
use App\Repositories\OperationLogs;
use App\Repositories\Project;
use Illuminate\Http\Request;
use App\Repositories\Document;

class OperationLogController extends Controller
{
    public function recently(Request $request)
    {
        $this->validate($request, [
            'limit'      => 'in:my,global,project',
            'per_page'   => 'between:1,100',
            'offset'     => 'between:0,100',
            'project_id' => 'integer',
            'catalog'    => 'integer',
        ]);

        $perPage   = (int)$request->input('per_page', 10);
        $offset    = (int)$request->input('offset', 0);
        $limit     = $request->input('limit', 'global');
        $projectId = (int)$request->input('project_id');
        $catalogId = (int)$request->input('catalog');

        /** @var OperationLogs $operationLogModel */
        $operationLogModel = OperationLogs::query();

        // 值查询目录下的项目日志
        if (empty($projectId) && !empty($catalogId)) {
            $projectIds =
                Project::where('catalog_id', $catalogId)->select(['id'])->pluck('id')->toArray();
            $operationLogModel->whereIn('project_id', $projectIds);
        }

        if ($limit == 'my') {
            $operationLogModel->where('user_id', \Auth::user()->id);
        } else if ($limit == 'project') {
            if ($projectId <= 0) {
                throw new \InvalidArgumentException('项目ID不能为空');
            }
            $operationLogModel->where('project_id', $projectId);
        }

        $operationLogs = $operationLogModel->orderBy('created_at', 'desc')
            ->limit($perPage)
            ->offset($offset)
            ->get();

        $pageIds = $operationLogs->pluck('page_id')->filter()->unique()->toArray();
        $docExternalIds = [];
        if (!empty($pageIds)) {
            $documents = Document::select('id', 'external_id')
                ->whereIn('id', $pageIds)
                ->get();
            $docExternalIds = $documents->pluck('external_id', 'id')->toArray();
        }
        foreach ($operationLogs as $operation) {
            $operation->doc_external_id = $docExternalIds[$operation->page_id] ?? '';
        }
        if ($request->wantsJson()) {
            return $operationLogs->transform(function (OperationLogs $operation) {
                return [
                    'id'           => $operation->id,
                    'user_id'      => $operation->user_id,
                    'username'     => $operation->context->username ?? '',
                    'face'         => user_face($operation->context->username ??
                        $operation->user_id),
                    'message'      => $operation->message,
                    'project_name' => $operation->context->project_name ?? '',
                    'project_id'   => $operation->project_id,
                    'doc_id'       => $operation->page_id,
                    'doc_external_id' => $operation->doc_external_id ?? '',
                    'doc_title'    => $operation->context->doc_title ?? '',
                    'created_at'   => $operation->created_at,
                ];
            });
        }

        return view('operations.recently', ['logs' => $operationLogs]);
    }
}