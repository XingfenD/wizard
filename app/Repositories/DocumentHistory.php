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
 *  1. New fillable field `version` to store document version in history record
 *  2. write history record with version when document is updated
 */

namespace App\Repositories;

use Carbon\Carbon;

/**
 * Class DocumentHistory
 *
 * @property integer                     $id
 * @property integer                     $page_id
 * @property integer                     $pid
 * @property string                      $title
 * @property string                      $description
 * @property string                      $content
 * @property integer                     $project_id
 * @property integer                     $user_id
 * @property string                      $type
 * @property string                      $status
 * @property integer                     $sort_level
 * @property string                      $sync_url
 * @property Carbon                      $last_sync_at
 * @property integer                     $operator_id
 * @property string                      $created_at
 * @property string                      $updated_at
 * @package App\Repositories
 * @property-read \App\Repositories\User $operator
 * @property-read \App\Repositories\User $user
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\DocumentHistory whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\DocumentHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\DocumentHistory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\DocumentHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\DocumentHistory whereOperatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\DocumentHistory wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\DocumentHistory wherePid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\DocumentHistory whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\DocumentHistory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\DocumentHistory whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\DocumentHistory whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\DocumentHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\DocumentHistory whereUserId($value)
 */
class DocumentHistory extends Repository
{
    protected $table = 'wz_page_histories';
    protected $fillable
        = [
            'page_id',
            'pid',
            'version',
            'title',
            'description',
            'content',
            'project_id',
            'user_id',
            'type',
            'status',
            'operator_id',
            'sort_level',
            'sync_url',
            'last_sync_at',
        ];

    /**
     * 记录文档历史
     *
     * @param Document $document
     *
     * @return DocumentHistory
     */
    public static function write(Document $document): DocumentHistory
    {
        // 计算当前文档的下一个版本号
        $lastVersion = self::where('page_id', $document->id)->max('version') ?? 0;
        $version = $lastVersion + 1;

        $history = self::create(array_only(
                $document->toArray(),
                (new static)->fillable) + [
                'operator_id' => $document->last_modified_uid,
                'page_id'     => $document->id,
                'version'     => $version,
            ]
        );

        $document->history_id = $history->id;
        $document->save();

        return $history;
    }

    /**
     * 文档所属用户
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 记录操作用户
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id', 'id');
    }
}