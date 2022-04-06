<?php


namespace App;


interface PlaceType
{
    const OUR_STOCK = 0;            //На нашем складе
    const BOOKED = 1;               //Забронирован
    const IN_CLIENT_ORDER = 2;      //В заказе у клиента
    const IN_CLIENT_REFUND = 3;     //В возврате от клиента
    const IN_VENDOR_REFUND = 4;     //В возврате поставщику
}
