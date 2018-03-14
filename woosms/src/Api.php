<?php
namespace BulkGate\WooSms;

use BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Api extends Extensions\Api\Api
{
    public function actionCampaignCustomerCount(Extensions\Api\IRequest $data)
    {
        $customers = new Customers($this->database);

        $this->sendResponse(new Extensions\Api\Response($customers->loadCount($data->filter), true));
    }

    public function actionCampaignCustomer(Extensions\Api\IRequest $data)
    {
        $customers = new Customers($this->database);

        $this->sendResponse(new Extensions\Api\Response($customers->load($data->filter), true));
    }
}
