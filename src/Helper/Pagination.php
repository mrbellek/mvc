<?php
declare(strict_types=1);

namespace MVC\Helper;

class Pagination {

    private string $baseUrl;    //base page url for links
    private int $perPage;       //items per page
    private int $total;         //total items available
    private int $maxRoom;       //how many spaces to show around current page before hiding behind ellipsis

    public function __construct(int $iTotal, int $iPerPage = 30, int $iMaxRoom = 10)
    {
        $this->baseUrl = $this->getCaller();
        $this->total = $iTotal;
        $this->perPage = $iPerPage;
        $this->maxRoom = $iMaxRoom;
    }

    public function getPage($objects, int $page)
    {
        $vars = $this->getVars($page);
        $offset = ($vars['current'] - 1) * $this->perPage;

        return array_slice($objects, $offset, $this->perPage, true);
    }

    public function getVars($page)
    {
        //calculate first & last page, sanitize user inputted current page nummer
        $last = ceil($this->total / $this->perPage);
        $first = 1;
        $page = ($page < $first || $page > $last ? $first : $page);

        return [
            'base'      => $this->baseUrl,
            'first'     => $first,
            'last'      => $last,
            'current'   => $page,
            'maxroom'   => $this->maxRoom,
        ];
    }

    private function getCaller()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $caller = '';
        if (!empty($backtrace[2])) {
            $class = strtolower(str_replace('MVC\\Controller\\', '', $backtrace[2]['class']));
            $function = $backtrace[2]['function'];
            $caller = '/' . $class . '/' . $function . '/';
        }

        return $caller;
    }
}