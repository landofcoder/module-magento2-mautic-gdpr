<?php

namespace Lof\MauticGdpr\Observer;

use Magento\Framework\Event\ObserverInterface;
use Lof\MauticGdpr\Queue\MessageQueues\CustomerDelete\Publisher;

class CustomerDeleteAfter implements ObserverInterface
{
    /**
    * @var Publisher
    */
    private $publisher;

    /**
     * @var \Lof\Mautic\Helper\Data
     */
    protected $helper;

    /**
     * @var \Lof\Mautic\Model\Mautic\Contact
     */
    protected $customerContact;

    /**
     * Construct customer save after observer
     *
     * @param \Lof\Mautic\Helper\Data $helper
     * @param \Lof\Mautic\Model\Mautic\Contact $customerContact
     * @param Publisher $publisher
     */
    public function __construct(
        \Lof\Mautic\Helper\Data $helper,
        \Lof\Mautic\Model\Mautic\Contact $customerContact,
        Publisher $publisher
    )
    {
        $this->helper = $helper;
        $this->customerContact = $customerContact;
        $this->publisher = $publisher;
    }

    /**
     * Sync customer data info to mautic
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isEnabled()) return $this;

        $customer = $observer->getCustomer();
        if ($customer->getId() && $this->helper->isCustomerIntegrationEnabled()) {
            $email = $customer->getEmail();
            try {
                $contacts = $this->customerContact->getList("email:$email", 0, 1, 'email', 'asc');
                if ($contacts && isset($contacts["total"]) && (int)$contacts["total"] > 0 && isset($contacts["contacts"])) {
                    foreach ($contacts["contacts"] as $contactId => $contact) {
                        if (!$this->helper->isAyncApi()) {
                            $this->customerContact->deleteRecord((int)$contactId);
                        } else {
                            $data = ["mautic_contact_id" => (int)$contactId];
                            $this->publisher->execute(
                                $this->helper->encodeData($data)
                            );
                        }
                    }
                }
            } catch (\Exception $e) {
                //error log
            }
        }
        return $this;
    }
}
