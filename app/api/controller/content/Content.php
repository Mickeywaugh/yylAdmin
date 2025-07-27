<?php
// +----------------------------------------------------------------------
// | yylAdmin 前后分离，简单轻量，免费开源，开箱即用，极简后台管理系统
// +----------------------------------------------------------------------
// | Copyright https://gitee.com/skyselang All rights reserved
// +----------------------------------------------------------------------
// | Gitee: https://gitee.com/skyselang/yylAdmin
// +----------------------------------------------------------------------

namespace app\api\controller\content;

use hg\apidoc\annotation as Apidoc;
use app\common\controller\BaseController;
use app\api\middleware\ContentSettingMiddleware;
use app\common\validate\content\ContentValidate;
use app\common\service\content\CategoryService;
use app\common\service\content\TagService;
use app\common\service\content\ContentService;

/**
 * @Apidoc\Title("lang(内容)")
 * @Apidoc\Group("content")
 * @Apidoc\Sort("100")
 */
class Content extends BaseController
{
    /**
     * 控制器中间件
     */
    protected $middleware = [ContentSettingMiddleware::class];

    /**
     * @Apidoc\Title("lang(内容分类列表)")
     * @Apidoc\Returned("list", type="tree", desc="分类树形", children={
     *   @Apidoc\Returned(ref={CategoryService::class,"info"}, field="category_id,category_pid,category_unique,category_name,image_url"),
     * })
     */
    public function category()
    {
        $where = where_disdel();
        $order = $this->order(['sort' => 'desc', 'category_id' => 'desc']);
        $field = 'category_unique,category_name,image_id';

        $data['list'] = CategoryService::list('tree', $where, $order, $field);

        return success($data);
    }

    /**
     * @Apidoc\Title("lang(内容标签列表)")
     * @Apidoc\Query(ref="pagingQuery")
     * @Apidoc\Query(ref="sortQuery")
     * @Apidoc\Query(ref={TagService::class,"edit"}, field="tag_id,tag_unique,tag_name")
     * @Apidoc\Returned(ref="pagingReturn")
     * @Apidoc\Returned("list", type="array", desc="标签列表", children={
     *   @Apidoc\Returned(ref={TagService::class,"info"}, field="tag_id,tag_unique,tag_name,image_url"),
     * })
     */
    public function tag()
    {
        $tag_id     = $this->param('tag_id/s', '');
        $tag_unique = $this->param('tag_unique/s', '');
        $tag_name   = $this->param('tag_name/s', '');
        if ($tag_id) {
            $where[] = ['tag_id', 'in', $tag_id];
        }
        if ($tag_unique) {
            $where[] = ['tag_unique', 'in', $tag_unique];
        }
        if ($tag_name) {
            $where[] = ['tag_name', 'like', '%' . $tag_name . '%'];
        }
        $where[] = where_disable();
        $where[] = where_delete();

        $order = ['sort' => 'desc', 'tag_id' => 'desc'];

        $field = 'tag_unique,tag_name,image_id';

        $data = TagService::list($where, $this->page(0), $this->limit(0), $this->order($order), $field);

        return success($data);
    }

    /**
     * @Apidoc\Title("lang(内容列表)")
     * @Apidoc\Query(ref="pagingQuery")
     * @Apidoc\Query(ref="sortQuery")
     * @Apidoc\Query(ref={CategoryService::class,"edit"}, field="category_id,category_unique")
     * @Apidoc\Query(ref={TagService::class,"edit"}, field="tag_id,tag_unique")
     * @Apidoc\Query(ref={ContentService::class,"edit"}, field="keywords,is_top,is_hot,is_rec")
     * @Apidoc\Returned(ref="pagingReturn")
     * @Apidoc\Returned("list", ref={ContentService::class,"info"}, type="array", field="content_id,unique,image_id,name,description,sort,hits,is_top,is_hot,is_rec,source,author,release_time,hits_show,image_url,category_names,tag_names")
     * @Apidoc\Returned("tops", type="array", desc="内容列表（置顶），字段同内容列表")
     * @Apidoc\Returned("hots", type="array", desc="内容列表（热门），字段同内容列表")
     * @Apidoc\Returned("recs", type="array", desc="内容列表（推荐），字段同内容列表")
     * @Apidoc\Returned("category", type="tree", desc="分类树形", children={
     *   @Apidoc\Returned(ref={CategoryService::class,"info"}, field="category_id,category_pid,category_unique,category_name,image_url"),
     * })
     * @Apidoc\Returned("tag", type="array", desc="标签列表", children={
     *   @Apidoc\Returned(ref={TagService::class,"info"}, field="tag_id,tag_unique,tag_name,image_url"),
     * })
     */
    public function list()
    {
        $category_id     = $this->param('category_id/s', '');
        $category_unique = $this->param('category_unique/s', '');
        $tag_id          = $this->param('tag_id/s', '');
        $tag_unique      = $this->param('tag_unique/s', '');
        $keywords        = $this->param('keywords/s', '');
        $is_top          = $this->param('is_top/s', '');
        $is_hot          = $this->param('is_hot/s', '');
        $is_rec          = $this->param('is_rec/s', '');

        $where = [];
        if ($category_id !== '') {
            $category_ids = explode(',', $category_id);
        }
        if ($category_unique !== '') {
            $category = CategoryService::info($category_unique, false);
            $category_ids[] = $category['category_id'] ?? '-1';
        }
        if ($category_id !== '' || $category_unique !== '') {
            $where[] = ['category_ids', 'in', $category_ids];
        }
        if ($tag_id !== '') {
            $tag_ids = explode(',', $tag_id);
        }
        if ($tag_unique !== '') {
            $tag = TagService::info($tag_unique, false);
            $tag_ids[] = $tag['tag_id'] ?? '-1';
        }
        if ($tag_id !== '' || $tag_unique !== '') {
            $where[] = ['tag_ids', 'in', $tag_ids];
        }
        if ($keywords) {
            $where[] = ['name|title|keywords|description', 'like', '%' . $keywords . '%'];
        }
        if ($is_top !== '') {
            $where[] = ['is_top', '=', $is_top];
        }
        if ($is_hot !== '') {
            $where[] = ['is_hot', '=', $is_hot];
        }
        if ($is_rec !== '') {
            $where[] = ['is_rec', '=', $is_rec];
        }

        $where_base = [['release_time', '<=', datetime()], where_disable(), where_delete()];
        $where = array_merge($where, $where_base);

        $where_top = $where_base;
        $where_top[] = ['is_top', '=', 1];

        $where_hot = $where_base;
        $where_hot[] = ['is_hot', '=', 1];

        $where_rec = $where_base;
        $where_rec[] = ['is_rec', '=', 1];

        $order = ['sort' => 'desc', 'content_id' => 'desc'];
        $field = 'unique,image_id,name,description,sort,hits,is_top,is_hot,is_rec,source,author,release_time';
        $data  = ContentService::list($where, $this->page(), $this->limit(), $this->order($order), $field);

        $data['tops']     = ContentService::list($where_top, 0, 7, [], $field, false)['list'] ?? [];
        $data['hots']     = ContentService::list($where_hot, 0, 7, [], $field, false)['list'] ?? [];
        $data['recs']     = ContentService::list($where_rec, 0, 7, [], $field, false)['list'] ?? [];
        $data['category'] = CategoryService::list('tree', where_disdel(), [], 'category_unique,category_name,image_id');
        $data['tag']      = TagService::list(where_disdel(), 0, 0, [], 'tag_unique,tag_name,image_id', false)['list'] ?? [];

        return success($data);
    }

    /**
     * @Apidoc\Title("lang(内容信息)")
     * @Apidoc\Query("content_id", type="string", require=true, default="", desc="内容id、编号")
     * @Apidoc\Returned(ref={ContentService::class,"info"})
     * @Apidoc\Returned("prev_info", type="object", desc="上一条", children={
     *   @Apidoc\Returned(ref={ContentService::class,"info"})
     * })
     * @Apidoc\Returned("next_info", type="object", desc="下一条", children={
     *   @Apidoc\Returned(ref={ContentService::class,"info"})
     * })
     */
    public function info()
    {
        $param = $this->params(['content_id/s' => '']);

        validate(ContentValidate::class)->scene('info')->check($param);

        $data = ContentService::info($param['content_id'], false);
        if (empty($data) || $data['is_disable'] || $data['is_delete'] || $data['release_time'] > datetime()) {
            return error(lang('内容不存在'));
        }

        $prev_info = ContentService::prevNext($data['content_id'], 'prev');
        $next_info = ContentService::prevNext($data['content_id'], 'next');

        $content_ids = [];
        if ($data['content_id'] ?? '') {
            $content_ids[] = $data['content_id'];
        }
        if ($prev_info['content_id'] ?? '') {
            $content_ids[] = $prev_info['content_id'];
        }
        if ($next_info['content_id'] ?? '') {
            $content_ids[] = $next_info['content_id'];
        }
        if ($content_ids) {
            $where[] = ['a.content_id', 'not in', $content_ids];
        }
        $where[] = ['release_time', '<=', datetime()];
        $where[] = where_disable();
        $where[] = where_delete();

        $list = ContentService::list($where, 0, 10, [], 'unique,image_id,name,create_time', false)['list'] ?? [];

        $data['prev_info'] = $prev_info;
        $data['next_info'] = $next_info;
        $data['list']      = $list;

        return success($data);
    }
}
