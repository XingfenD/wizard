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
 *  1. New fillable field `external_id` to store document external id
 *  2. Add method `idFromExternalID` and `externalIdFromID` to query id from external_id and vice versa
 *  3. Add method `findByExternalID` to find document by external_id
 */

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Page
 *
 * @property integer                                                                      $id
 * @property integer                                                                      $pid
 * @property string                                                                       $title
 * @property string                                                                       $description
 * @property string                                                                       $content
 * @property integer                                                                      $project_id
 * @property integer                                                                      $user_id
 * @property integer                                                                      $last_modified_uid
 * @property integer                                                                      $history_id
 * @property integer                                                                      $type
 * @property integer                                                                      $status
 * @property integer                                                                      $sort_level
 * @property string                                                                       $sync_url
 * @property Carbon                                                                       $last_sync_at
 * @property Carbon                                                                       $created_at
 * @property Carbon                                                                       $updated_at
 * @property string                                                                       $external_id
 * @package App\Repositories
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Repositories\Attachment[] $attachments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Repositories\Comment[]    $comments
 * @property-read \App\Repositories\User                                                  $lastModifiedUser
 * @property-read \App\Repositories\Document                                              $parentPage
 * @property-read \App\Repositories\Project                                               $project
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Repositories\Document[]   $subPages
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Repositories\Tag[]        $tags
 * @property-read \App\Repositories\User                                                  $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Repositories\Document onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Repositories\Document withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Repositories\Document withoutTrashed()
 * @mixin \Eloquent
 * @property \Carbon\Carbon|null                                                          $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Document whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Document whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Document whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Document whereHistoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Document whereLastModifiedUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Document wherePid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Document whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Document whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Document whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Document whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Document whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Document whereUserId($value)
 */
class Document extends Repository
{

    use SoftDeletes;

    const TYPE_DOC     = 1;
    const TYPE_SWAGGER = 2;
    const TYPE_TABLE   = 3;

    /**
     * 状态：正常
     */
    const STATUS_NORMAL = 1;
    /**
     * 状态：已过时
     */
    const STATUS_OUTDATED = 2;

    protected $table = 'wz_pages';
    protected $fillable
        = [
            'pid',
            'title',
            'description',
            'content',
            'project_id',
            'user_id',
            'last_modified_uid',
            'history_id',
            'type',
            'status',
            'sort_level',
            'sync_url',
            'last_sync_at',
            'external_id'
        ];

    public $dates = ['deleted_at'];

    /**
     * 文档恢复
     *
     * @param Document        $document
     * @param DocumentHistory $history
     *
     * @return Document
     */
    public static function recover(Document $document, DocumentHistory $history): Document
    {
        $document->pid               = $history->pid;
        $document->title             = $history->title;
        $document->description       = $history->description;
        $document->content           = $history->content;
        $document->last_modified_uid = $history->operator_id;
        $document->type              = $history->type;
        $document->status            = $history->status;
        $document->sort_level        = $history->sort_level;
        $document->sync_url          = $history->sync_url;
        $document->last_sync_at      = $history->last_sync_at;

        $document->save();

        return $document;
    }

    /**
     * 所属的项目
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    /**
     * 页面所属的用户
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 最后修改页面的用户ID
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lastModifiedUser()
    {
        return $this->belongsTo(User::class, 'last_modified_uid', 'id');
    }

    /**
     * 上级页面
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parentPage()
    {
        return $this->belongsTo(self::class, 'pid', 'id');
    }

    /**
     * 子页面
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subPages()
    {
        return $this->hasMany(self::class, 'pid', 'id');
    }

    /**
     * 文档下的评论
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'page_id', 'id');
    }

    /**
     * 页面下所有的附件
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'page_id', 'id');
    }

    /**
     * 页面的标签
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'wz_page_tag', 'page_id', 'tag_id');
    }

    /**
     * 判断当前文档是否为 Markdown 文档
     *
     * @return bool
     */
    public function isMarkdown()
    {
        return (int)$this->type === self::TYPE_DOC;
    }

    /**
     * 判断当前文档是否为 Swagger 文档
     *
     * @return bool
     */
    public function isSwagger()
    {
        return (int)$this->type === self::TYPE_SWAGGER;
    }

    /**
     * 判断当前文档是否为 Table 文档
     *
     * @return bool
     */
    public function isTable()
    {
        return (int)$this->type === self::TYPE_TABLE;
    }

    /**
     * 通过 external_id 获取文档 ID
     *
     * @param string $externalId     外部 ID
     * @param bool   $failOnNotFound 未找到时是否抛出异常
     *
     * @return int|null
     */
    public static function idFromExternalID($externalId, $failOnNotFound = true)
    {
        $query = self::select('id')->where('external_id', $externalId);

        if ($failOnNotFound) {
            return $query->firstOrFail()->id;
        }

        $document = $query->first();
        return $document ? $document->id : null;
    }

    /**
     * 通过 ID 获取 external_id
     *
     * @param int    $id             文档 ID
     * @param bool   $failOnNotFound 未找到时是否抛出异常
     *
     * @return string|null
     */
    public static function externalIdFromID($id, $failOnNotFound = true)
    {
        $query = self::select('external_id')->where('id', $id);

        if ($failOnNotFound) {
            return $query->firstOrFail()->external_id;
        }

        $document = $query->first();
        return $document ? $document->external_id : null;
    }

    /**
     * 通过 external_id 获取文档实例
     *
     * @param int    $projectId      项目 ID
     * @param string $externalId     外部 ID
     * @param array  $columns        要查询的字段
     *
     * @return Document
     */
    public static function findByExternalID($projectId, $externalId, $columns = ['*'])
    {
        return self::where('project_id', $projectId)->where('external_id', $externalId)->firstOrFail($columns);
    }

}