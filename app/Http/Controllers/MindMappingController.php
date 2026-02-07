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
 *  1. Fix Issue#115 (original project) in Commit 59e746dd:
 *        Guests can see mind_mapping in read_only mode now without log_in
 */

namespace App\Http\Controllers;


use App\Repositories\Widget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 思维导图
 *
 * @package App\Http\Controllers
 */
class MindMappingController extends Controller
{
    public function editor(Request $request)
    {
        // TODO: priveledge check by project (new field `project` to database)
        $refId = $request->input('ref_id');
        $user = Auth::user();
        $widget = null;

        if (!empty($refId)) {
            /** @var Widget $widget */
            $widget = Widget::where('type', Widget::TYPE_MIND_MAPPING)->where('ref_id', $refId)->firstOrFail();
        }

        // 如果用户未登录，强制只读模式
        $readonly = empty($user) ? 1 : (int)$request->input('readonly', 1);

        return view(
            'mind-mapping.editor',
            [
                'widget'   => $widget ?? null,
                'readonly' => $readonly,
            ]
        );
    }

    /**
     * 保存思维导图
     *
     * @param Request $request
     * @return array
     * @throws \Throwable
     */
    public function save(Request $request)
    {
        $refId = $request->input('ref_id');
        if (empty($refId)) {
            $refId = $this->createRefId();
        }

        /** @var Widget $widget */
        $widget = Widget::where('type', Widget::TYPE_MIND_MAPPING)->where('ref_id', $refId)->firstOrNew(
            [
                'ref_id' => $refId
            ]
        );
        if (!$widget->exists) {
            $widget->type = Widget::TYPE_MIND_MAPPING;
            $widget->user_id = Auth::user()->id;
            $widget->ref_id = $refId;
        }

        $widget->name = $request->input('name');
        $widget->content = $request->input('content');
        $widget->description = $request->input('description');
        $widget->operator_id = Auth::user()->id;

        $widget->saveOrFail();

        return [
            'ref_id' => $refId,
            'url'    => wzRoute('mind-mapping:editor', ['ref_id' => $refId]),
            'name'   => $widget->name,
        ];
    }

    /**
     * 创建 Widget ref_id
     *
     * @return string
     */
    private function createRefId()
    {
        return md5(microtime(true) . '_' . Auth::user()->id . uniqid('mind-mapping'));
    }
}