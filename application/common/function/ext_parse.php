<?php
function parse_at_users($content, $disabel_hight = false)
{
    $content = $content . ' ';
    //找出被AT的用户
    $at_users = get_at_users($content);

    //将@用户替换成链接
    foreach ($at_users as $e) {
        $user = D('Member')->where(array('uid' => $e))->find();
        if ($user) {
            $query_user = query_user(array('space_url', 'avatar32', 'nickname'), $user['uid']);
            if (modC('HIGH_LIGHT_AT', 1, 'Weibo') && !$disabel_hight) {
                $content = str_replace("[at:$e]", " <a class='user-at hl ' ucard=\"$user[uid]\" href=\"$query_user[space_url]\"><img src=\"$query_user[avatar32]\">@$query_user[nickname] </a> ", $content);
            } else {
                $content = str_replace("[at:$e]", " <a ucard=\"$user[uid]\" href=\"$query_user[space_url]\">@$query_user[nickname] </a> ", $content);
            }

        }
    }

    //返回替换的文本
    return $content;
}

/**
 * get_at_usernames  获取@用户的用户名
 * @param $content
 * @return array
 * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
 */
function get_at_users($content)
{
    //正则表达式匹配
    $user_pattern = '/\[at:(\d*)\]/';
    preg_match_all($user_pattern, $content, $users);

    //返回用户名列表
    return array_unique($users[1]);
}

/**
 * get_at_uids  获取@的用户的uid
 * @param $content
 * @return array
 * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
 */
function get_at_uids($content)
{
    $uids = get_at_users($content);
    return $uids;
}

function parse_comment_content($content)
{
    //就目前而言，评论内容和微博的格式是一样的。
    return parse_weibo_content($content);
}

function parse_comment_mob_content($content)
{
    //就目前而言，评论内容和微博的格式是一样的。
    return parse_weibo_mob_content($content);
}

function parse_weibo_content($content)
{
    $content = shorten_white_space($content);

    if (modC('WEIBO_BR', 0, 'Weibo')) {
        $content = str_replace('/br', '<br/>', $content);
        $content = str_replace('/nb', ' ', $content);

    } else {
        $content=str_replace('/br','',$content);
        $content=str_replace('/nb','',$content);
    }
    $content = parse_url_link($content);
    $content = parse_expression($content);
    $content = parseWeiboContent($content);
    return $content;
}
function parse_weibo_mob_content($content)
{
    $content = shorten_white_space($content);
    $content = op_t($content,false);
    $content = parse_url_link($content);
    $content = parseWeiboContent($content);
    return $content;
}

function shorten_white_space($content)
{
    $content = preg_replace('/\s+/', ' ', $content);
    return $content;
}

function parse_url_link($content)
{
    $content = preg_replace("#((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#ie",
        "'<a class=\"label label-badge\" href=\"$1\" target=\"_blank\"><i class=\"icon-link\" title=\"$1\"></i></a>$4'", $content
    );
    return $content;
}

function parseWeiboContent($content)
{
    hook('parseWeiboContent', array('content' => &$content));
    hook('parseContent',array('content'=>&$content));
    return $content;

}


function parse_content($content)
{
    return $content;
}