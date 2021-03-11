<?php
// .-----------------------------------------------------------------------------------
// |  Software: [HDPHP framework]
// |   Version: 2013.01
// |      Site: http://www.hdphp.com
// |-----------------------------------------------------------------------------------
// |    Author: 向军 <houdunwangxj@gmail.com>
// | Copyright (c) 2012-2013, http://houdunwang.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------
// |   License: http://www.apache.org/licenses/LICENSE-2.0
// '-----------------------------------------------------------------------------------

/**
 * 分页处理类
 * @package     tools_class
 * @author      后盾向军 <houdunwangxj@gmail.com>
 */
class Page{
    static $staticTotalPage=null;//总页数
    static $staticUrl=null;//当前url
    static $fix='';//后缀
    public $totalRow; //总条数
    public $totalPage; //总页数
    public $arcRow; //每页显示数
    public $pageRow; //每页显示页码数
    public $style; //界面风格
    public $selfPage; //当前页
    public $url; //页面地址
    public $args; //页面传递参数
    public $startId; //当前页开始ID
    public $endId; //当前页末尾ID
    public $desc = array(); //文字描述

    /**
     * 构造函数
     * @param number $total       总条数
     * @param number $row         每页显示条数
     * @param number $pageRow    显示页码数量
     * @param number $style       页码样式
     * @param array $desc        描述文字
     */

    function __construct($total, $row = '', $pageRow = '', $style = '', $desc = '') {
        $this->totalRow = $total; //总条数
        $this->arcRow = empty($row) ? C("PAGE_SHOW_ROW") : $row; //每页显示条数
        $this->pageRow = empty($pageRow) ? C('PAGE_ROW') : $pageRow; //显示页码数量
        $this->style = empty($style) ? C('PAGE_STYLE') : $style; //页码样式
        $this->totalPage = ceil($this->totalRow / $this->arcRow); //总页数
        self::$staticTotalPage=$this->totalPage;//总页数
        $this->selfPage = min($this->totalPage, empty($_GET[C("PAGE_VAR")]) ? 1 : max(1, (int) $_GET[C("PAGE_VAR")])); //当前页
        $this->url = is_null(self::$staticUrl)?$this->setUrl():self::$staticUrl; //配置url地址
        $this->startId = ($this->selfPage - 1) * $this->arcRow + 1; //当前页开始ID
        $this->endId = min($this->selfPage * $this->arcRow, $this->totalRow); //当前页结束ID
        $this->desc = $this->desc($desc);
    }

    /**
     *
     * 配置描述文字
     * @param array $desc
     * "pre"=>"上一页"
     * "next"=>"下一页"
     * "pres"=>"前十页"
     * "nexts"=>"下十页"
     * "first"=>"首页"
     * "end"=>"尾页"
     * "unit"=>"条"
     */
    private function desc($desc) {
        $this->desc = array_change_key_case(C('PAGE_DESC'));
        if (empty($desc) || !is_array($desc))
            return $this->desc;

        function filter($v) {
            return !empty($v);
        }

        return array_merge($this->desc, array_filter($desc, "filter"));
    }

    //配置URL地址
    protected function setUrl() {
        $get = $_GET;
        unset($get["a"]);
        unset($get['c']);
        unset($get["m"]);
        unset($get[C("PAGE_VAR")]);
        $url_type = C("URL_TYPE");
        switch ($url_type) {
            case 1:
                $url = __METH__ . '/';
                foreach ($get as $k => $v) {
                    $url.=$k . '/' . $v . '/';
                }
                return rtrim($url, '/') . '/' . C("PAGE_VAR") . '/';
                break;
            case 2:
                $url = __METH__ . '&';
                foreach ($get as $k => $v) {
                    $url.=$k . "=" . $v . '&';
                }
                return $url . C("PAGE_VAR") . '=';
        }
    }

    //SQL的limit语句
    public function limit() {
        return array("limit" => max(0, ($this->selfPage - 1) * $this->arcRow) . "," . $this->arcRow);
    }

    //上一页
    protected function pre() {
        if ($this->selfPage > 1 && $this->selfPage <= $this->totalPage) {
            return "<a href='" . $this->url . ($this->selfPage - 1) . page::$fix."' Tool='pre'>{$this->desc['pre']}</a>&nbsp;";
        }
        return "<span class='close'>{$this->desc['pre']}</span>&nbsp;";
    }

    //下一页
    public function next() {
        $next = $this->desc ['next'];
        if ($this->selfPage < $this->totalPage) {
            return "<a href='" . $this->url . ($this->selfPage + 1) . self::$fix."' Tool='next'>{$next}</a>&nbsp;";
        }
        return "<span class='close'>{$next}</span>&nbsp;";
    }

    //列表项
    private function pageList() {
        //页码
        $pageList = '';
        $start = max(1, min($this->selfPage - ceil($this->pageRow / 2), $this->totalPage - $this->pageRow));
        $end = min($this->pageRow + $start, $this->totalPage);
        if ($end == 1)//只有一页不显示页码
            return '';
        for ($i = $start; $i <= $end; $i++) {
            if ($this->selfPage == $i) {
                $pageList [$i] ['url'] = "";
                $pageList [$i] ['str'] = $i;
                continue;
            }
            $pageList [$i] ['url'] = $this->url . $i.page::$fix;
            $pageList [$i] ['str'] = $i;
        }
        return $pageList;
    }

    //文字页码列表
    public function strList() {
        $arr = $this->pageList();
        $str = '';
        if (empty($arr))
            return;
        foreach ($arr as $v) {
            $str .= empty($v ['url']) ? "<strong Tool='selfpage'>" . $v ['str'] . "</strong>" : "<a href={$v['url']}>{$v['str']}</a>&nbsp;";
        }
        return $str;
    }

    //图标页码列表
    public function picList() {
        $str = '';
        $first = $this->selfPage == 1 ? "" : "<a href='{$this->url}1".page::$fix."' Tool='picList'><span><<</span></a>&nbsp;";
        $end = $this->selfPage >= $this->totalPage ? "" : "<a href='{$this->url}{$this->totalPage}".self::$fix."'  Tool='picList'><span>>></span></a>&nbsp;";
        $pre = $this->selfPage <= 1 ? "" : "<a href='{$this->url}" . ($this->selfPage - 1) . self::$fix."'  Tool='picList'><span><</span></a>&nbsp;";
        $next = $this->selfPage >= $this->totalPage ? "" : "<a href='{$this->url}" . ($this->selfPage + 1) . self::$fix."'  Tool='picList'><span>></span></a>&nbsp;";

        return $first . $pre . $next . $end;
    }

    //选项列表
    public function select() {
        $arr = $this->pageList();
        if (!$arr) {
            return '';
        }
        $str = "<select name='page' Tool='page_select' onchange='
		javascript:
		location.href=this.options[selectedIndex].value;'>";
        foreach ($arr as $v) {
            $str .= empty($v ['url']) ? "<option value='{$this->url}{$v['str']}".self::$fix."' selected='selected'>{$v['str']}</option>" : "<option value='{$v['url']}'>{$v['str']}</option>";
        }
        return $str . "</select>";
    }

    //输入框
    public function input() {
        $str = "<input id='pagekeydown' type='text' name='page' value='{$this->selfPage}' Tool='pageinput' onkeydown = \"javascript:
					if(event.keyCode==13){
						location.href='{$this->url}".self::$fix."'+this.value;
					}
				\"/>
				<button Tool='pagebt' onclick = \"javascript:
					var input = document.getElementById('pagekeydown');
					location.href='{$this->url}".self::$fix."'+input.value;
				\">进入</button>
";
        return $str;
    }

    //前几页
    public function pres() {
        $num = max(1, $this->selfPage - $this->pageRow);
        return $this->selfPage > $this->pageRow ? "<a href='" . $this->url . $num .self::$fix. "' Tool='pres'>前{$this->pageRow}页</a>&nbsp" : "";
    }

    //后几页
    public function nexts() {
        $num = min($this->totalPage, $this->selfPage + $this->pageRow);
        return $this->selfPage + $this->pageRow < $this->totalPage ? "<a href='" . $this->url . $num . self::$fix."' Tool='nexts'>后{$this->pageRow}页</a>&nbsp" : "";
    }

    //首页
    public function first() {
        $first = $this->desc ['first'];
        return $this->selfPage - $this->pageRow > 1 ? "<a href='" . $this->url . "1'".self::$fix." Tool='first'>{$first}</a>&nbsp;" : "";
    }

    //末页
    public function end() {
        $end = $this->desc ['end'];
        return $this->selfPage < $this->totalPage - $this->pageRow ? "<a href='" . $this->url . $this->totalPage . self::$fix."' Tool='end'>{$end}</a>&nbsp;" : "";
    }

    //当前页记录
    public function nowPage() {
        return "<span Tool='nowPage'>".L("page_nowPage") . "{$this->startId}-{$this->endId}{$this->desc['unit']}</span>";
    }

    //count统计
    public function count() {
        return "<span Tool='count'>[" . L("page_count1") . "{$this->totalPage}" . L("page_count2") . "] [{$this->totalRow}" . L("page_count3") . "]</span>";
    }
    /**
     * 返回所有分页信息
     * @return Array
     */
    public function getAll() {
        $show = array();
        $show['count'] = $this->count();
        $show['first'] = $this->first();
        $show['pre'] = $this->pre();
        $show['pres'] = $this->pres();
        $show['strList'] = $this->strList();
        $show['nexts'] = $this->nexts();
        $show['next'] = $this->next();
        $show['end'] = $this->end();
        $show['nowPage'] = $this->nowPage();
        $show['select'] = $this->select();
        $show['input'] = $this->input();
        $show['picList'] = $this->picList();
        return $show;
    }

    //页码风格
    public function show($s = '') {
        if (empty($s)) {
            $s = $this->style;
        }
        switch ($s) {
            case 1 :
                return "{$this->count()}{$this->first()}{$this->pre()}{$this->pres()}{$this->strList()}{$this->nexts()}{$this->next()}{$this->end()}
                {$this->nowPage()}{$this->select()}{$this->input()}{$this->picList()}";
            case 2 :
                return $this->pre() . $this->strList() . $this->next();
            case 3 :
                return "<span Tool='total'>" . L("page_show_case1") . ":{$this->totalRow}
                {$this->desc['unit']}&nbsp;</span>" . $this->picList() . $this->select();
            case 4:
                return $this->first().$this->pre().$this->strList().$this->next().$this->end();
        }
    }

}