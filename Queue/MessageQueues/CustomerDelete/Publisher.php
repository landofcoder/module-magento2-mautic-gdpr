<?php declare(strict_types=1);

namespace Lof\MauticGdpr\Queue\MessageQueues\CustomerDelete;

use Lof\Mautic\Queue\MessageQueues\AbstractPublisher;

class Publisher extends AbstractPublisher
{
    /**
     * {@inheritdoc}
     */
    protected $_topic_name = 'mautic.magento.customer.delete';
}
