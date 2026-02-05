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
 *  1. Use Document external id instead of page id (comment module)
 */

namespace App\Http\Controllers;


use App\Events\CommentCreated;
use App\Notifications\CommentReplied;
use App\Notifications\DocumentCommented;
use App\Policies\ProjectPolicy;
use App\Repositories\Comment;
use App\Repositories\Document;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * 发表评论
     *
     * @param Request $request
     * @param         $id.              - project_id
     * @param         $page_external_id
     *
     * @return array
     */
    public function publish(Request $request, $id, $page_external_id)
    {
        $content = $request->input('content');
        $this->validateParameters(
            [
                'project_id'        => $id,
                'page_external_id'  => $page_external_id,
                'content'           => $content,
            ],
            [
                'project_id'        => "required|integer|min:1|in:{$id}|project_exist",
                'page_external_id'  => "required|string|in:{$page_external_id}|page_exist_by_external_id:{$id}",
                'content'           => 'required|between:1,10000',
            ],
            [
                'content.required' => '评论内容不能为空',
                'content.between'  => '评论内容最大不能超过10000字符',
            ]
        );

        $policy = new ProjectPolicy();
        if (!$policy->view(\Auth::user(), $id)) {
            abort(404);
        }
        debugLog('page_external_id'.$page_external_id);
        debugLog('page_id'.Document::idFromExternalID($page_external_id));

        $comment = Comment::create([
            'content'           => comment_filter($content),// TODO XSS过滤
            'user_id'           => \Auth::user()->id,
            'reply_to_id'       => 0,
            'page_id'           => Document::idFromExternalID($page_external_id),
        ]);

        event(new CommentCreated($comment));

        return [
            'id' => $comment->id
        ];
    }

}