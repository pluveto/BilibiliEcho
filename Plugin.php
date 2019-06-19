<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
require "Feed.php";
/**
 * Bilibili 动态同步插件。
 *
 * @package BilibiliEcho
 * @author  pluvet
 * @version 1.0.0
 * @link https://www.pluvet.com
 */
class BilibiliEcho_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @throws Typecho_Db_Exception
     */
    public static function activate()
    {
        // Typecho_Plugin::factory('Widget_Archive') -> header = array(__CLASS__, 'header');        
        return '插件启用成功，别忘了设置一下哟';
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @access public
     * @return void
     */
    public static function deactivate()
    { }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {

        $uid = new Typecho_Widget_Helper_Form_Element_Text(
            'uid',
            NULL,
            345852729,
            _t('用户id')
        );
        $form->addInput($uid);

        $expireTime = new Typecho_Widget_Helper_Form_Element_Text(
            'expireTime',
            NULL,
            24,
            _t('缓存时间）',
            _t('单位为小时（建议大于12小时，以减轻服务器负担。如果设置过低可能无效，因为 api 服务器本身也带有缓存。'))
        );
        $form->addInput($expireTime);

        $count = new Typecho_Widget_Helper_Form_Element_Text(
            'count',
            NULL,
            5,
            _t('显示条数')
        );
        $form->addInput($count);

        $share = new Typecho_Widget_Helper_Form_Element_Radio('share', array(
            0   =>  _t('否'),
            1   =>  _t('是')
        ), 0, _t('显示转发内容'));
        $form->addInput($share->addRule('enum', _t('请选择一种'), array(0, 1)));

    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    { }

    public static function renderDynamics()
    {
        $uid = Typecho_Widget::widget('Widget_Options')->plugin('BilibiliEcho')->uid;
        if (!is_numeric($uid)) echo "错误：uid 必须是数字";
        $expireTime = Typecho_Widget::widget('Widget_Options')->plugin('BilibiliEcho')->expireTime;
        $count = intval(Typecho_Widget::widget('Widget_Options')->plugin('BilibiliEcho')->count);
        $share = intval(Typecho_Widget::widget('Widget_Options')->plugin('BilibiliEcho')->share);



        $url = "https://rsshub.app/bilibili/user/dynamic/" . $uid;
        $spaceUrl = "https://space.bilibili.com/" . $uid;
        Feed::$cacheDir = __DIR__ . '/tmp';
        Feed::$cacheExpire = $expireTime . ' hours';
        $rss = Feed::loadRss($url);

        $author = str_replace(" 的 bilibili 动态", "", $rss->title);

        // echo 'Title: ', $rss->title;
        // echo 'Description: ', $rss->description;
        // echo 'Link: ', $rss->link;

        $counter = 0;
        ?>
    <div class="widget">
        <div class="heading-title">
            Dynamics
        </div>
        <ul class="list--withIcon list">

            <?php foreach ($rss->item as $item) :
                if ($counter == $count) break;
                if (strpos($item->description, "转发自") > 0 && !$share) {
                    continue;
                }
                $item->description = preg_replace("/\[.*?\]/", "", $item->description);
                $item->description = str_replace("<img", '<img style="border-radius: 4px;"', $item->description);
                ?>
                <li class="list-item">
                    <?php
                    echo '' .
                        '<p style="font-size: .9em;"><a style="color: #5DADE2;" href="' . $item->link . '">@' . htmlspecialchars($author) . '</a>&nbsp;' .
                        $item->description . '</p>';
                    ?>
                    <small style="color: #aaa; float: right; font-size: .6em;"><time class="lately-a" datetime="<?php echo date("Y-m-d H:i:s", intval($item->timestamp)); ?>" itemprop="datePublished"><?php echo date("Y-m-d H:i:s", $item->timestamp); ?></time></small>
                    <br />
                </li>
                <?php
                $counter++;
            endforeach; ?>

        </ul>
    </div>
<?php


}
}
