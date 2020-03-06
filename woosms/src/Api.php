<?php

namespace BulkGate\WooSms;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Extensions;
use BulkGate\Extensions\Api\IRequest;
use BulkGate\Extensions\Api\Response;

class Api extends Extensions\Api\Api
{
    public function actionCampaignCustomerCount(IRequest $data)
    {
        $customers = new Customers($this->database);

        $this->sendResponse(new Response($customers->loadCount($data->filter), true));
    }


    public function actionCampaignCustomer(IRequest $data)
    {
        $customers = new Customers($this->database);

        $this->sendResponse(new Response($customers->load($data->filter), true));
    }
}
