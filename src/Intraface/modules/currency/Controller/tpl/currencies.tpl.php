<table class="stripe">
    <thead>
    <tr>
        <th><?php e(t('Currency')); ?></th>
        <th><?php e(t('ISO')); ?></th>
        <th><?php e(t('Exchange rate product price')); ?></th>
        <th><?php e(t('Exchange rate payments')); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($currencies as $currency) : ?>
    <tr>
        <td><?php e($currency->getType()->getDescription()); ?></td>
        <td><?php e($currency->getType()->getIsoCode()); ?></td>
        <td><?php  $rate = $currency->getProductPriceExchangeRate();
        if ($rate === false) :
            e(t('Not given'));
        else :
            e($rate->getRate()->getAsLocal('da_dk').' ('.$rate->getDateUpdated()->getAsLocal('da_dk').')');
        endif; ?> <a class="edit" href="<?php e(url($currency->getId().'/exchangerate/productprice/update')) ?>"><?php e(t('Update')); ?></a></td>
        <td><?php  $rate = $currency->getPaymentExchangeRate();
        if ($rate === false) :
            e(t('Not given'));
        else :
            e($rate->getRate()->getAsLocal('da_dk').' ('.$rate->getDateUpdated()->getAsLocal('da_dk').')');
        endif; ?> <a class="edit" href="<?php e(url($currency->getId().'/exchangerate/payment/update')) ?>"><?php e(t('Update')); ?></a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>