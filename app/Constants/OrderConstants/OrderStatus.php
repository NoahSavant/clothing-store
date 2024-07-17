<?php

namespace App\Constants\OrderConstants;

class OrderStatus
{
    const PENDING = 0;

    const PAYING = 1;

    const SHIPPING = 2;

    const SHIPPED = 3;

    const SUCCESS = 4; 

    const CANCEL = 5;

    public static function getContent($mime)
    {
        $parent = null;
        switch ($mime) {
            case self::PENDING:
                $parent = "Đang chuẩn bị";
                break;
            case self::PAYING:
                $parent = "Chờ chi trả";
                break;
            case self::SHIPPING:
                $parent = "Đang giao hàng";
                break;
            case self::SHIPPED:
                $parent = "Đã giao hàng";
                break;
            case self::SUCCESS:
                $parent = "Đơn hàng hoàn thành";
                break;
            case self::CANCEL:
                $parent = "Đơn hàng bị hủy";
                break;
            default:
                return "Unknown";
        }

        return $parent;
    }
}
