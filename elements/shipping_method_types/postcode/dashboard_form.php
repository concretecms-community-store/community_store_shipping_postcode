<?php
defined('C5_EXECUTE') or die("Access Denied.");
extract($vars);

$co = Core::make('helper/lists/countries');

?>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <?= $form->label('country', t("Country")); ?>
            <?php echo $form->select('country', $co->getCountries(), $smtm->getCountry(), array('classes'=>'form-control'))?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-6">
        <div class="form-group">
            <?= $form->label('minimumAmount', t("Minimum Purchase Amount for this rate to apply")); ?>
            <div class="input-group">
                <div class="input-group-addon input-group-text">
                    <?= Config::get('community_store.symbol'); ?>
                </div>
                <?= $form->text('minimumAmount', $smtm->getMinimumAmount() ? $smtm->getMinimumAmount() : '0'); ?>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-6">
        <div class="form-group">
            <?= $form->label('maximumAmount', t("Maximum Purchase Amount for this rate to apply")); ?>
            <div class="input-group">
                <div class="input-group-addon input-group-text">
                    <?= Config::get('community_store.symbol'); ?>
                </div>
                <?= $form->text('maximumAmount', $smtm->getMaximumAmount() ? $smtm->getMaximumAmount() : '0'); ?>
            </div>
        </div>
    </div>
</div>

<h3><?php echo t('Postcode mapping'); ?></h3>

<?php if (empty($rates)) {
    $rates = array();
    $rates[] = array('rate' => '', 'postcodes' => '', 'label' => '');
} ?>

<div id="postcoderows">

    <?php
    foreach ($rates as $rate) { ?>
        <div class="row clearfix pc-entry">
            <div class="col-md-4">
                <div class="form-group-inline">
                    <label><i class="fa fa-arrows"></i> <?php echo t('Postcode range/list'); ?></label>
                </div>
                <div class="form-group">
                    <input name="postcodes[]" type="text" class="form-control ccm-input-text"
                           value="<?php echo $rate['postcodes']; ?>"/>

                    <div class="help-block"><?= t('Enter a comma separated list of postcodes or ranges, e.g. 1001,1002,2000-3000,4001'); ?></div>

                </div>


            </div>

            <div class="col-md-7">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group-inline">
                            <label><?php echo t('Standard'); ?></label>
                        </div>
                        <div class="form-group">
                            <input name="label[]" type="text" class="form-control ccm-input-text"
                                   value="<?php echo $rate['label']; ?>" placeholder="<?php echo t('Label'); ?>"/>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon input-group-text">
                                    <?= Config::get('community_store.symbol'); ?>
                                </div>
                                <input name="rate[]" type="text" class="form-control ccm-input-text" placeholder="<?php echo t('Shipping Rate'); ?>" value="<?php echo $rate['rate']; ?>"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon input-group-text">
                                    <?= Config::get('community_store.symbol'); ?>
                                </div>
                                <input name="free[]" type="text" class="form-control ccm-input-text" placeholder="<?php echo t('Free Threshold'); ?>" value="<?php echo isset($rate['free']) ? $rate['free'] : ''; ?>"/>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-6">
                        <div class="form-group-inline">
                            <label><?php echo t('Express'); ?></label>
                        </div>

                        <div class="form-group">
                            <input name="expresslabel[]" type="text" class="form-control ccm-input-text" value="<?php echo isset($rate['expresslabel']) ? $rate['expresslabel'] : ''; ?>"  placeholder="<?php echo t('Label'); ?>"/>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon input-group-text">
                                    <?= Config::get('community_store.symbol'); ?>
                                </div>
                                <input name="expressrate[]" type="text" class="form-control ccm-input-text" placeholder="<?php echo t('Shipping Rate'); ?>" value="<?php echo isset($rate['free']) ?  $rate['expressrate'] : ''; ?>"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon input-group-text">
                                    <?= Config::get('community_store.symbol'); ?>
                                </div>
                                <input name="freeexpress[]" type="text" class="form-control ccm-input-text" placeholder="<?php echo t('Free Threshold'); ?>" value="<?php echo isset($rate['free']) ?  $rate['freeexpress'] : ''; ?>"/>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-1">
                <button class="btn btn-danger remove-row"><i class="fa fa-trash"></i></button>
            </div>
            <hr style="clear: both"/>
        </div>

    <?php } ?>
</div>

<p>
    <button id="addrow" class="btn btn-sm btn-primary"><?php echo t('Add Another'); ?></button>
</p>

<script>
    $(document).ready(function () {

        $('#postcoderows').sortable({axis: 'y'});

        $('#addrow').click(function (e) {
            var el = $('#postcoderows .pc-entry:first-child').clone();
            el.find('input').val('');
            el.find('.remove-row').removeClass('hidden');
            el.appendTo('#postcoderows');

            $('html, body').animate({
                scrollTop: $(this).offset().top
            }, 1000);

            e.preventDefault();
        })
    })


    $('#postcoderows').on('click', '.remove-row', function (e) {
        if ($('#postcoderows .pc-entry').size() == 1) {
            $('#postcoderows .pc-entry:first-child').find('input').val('');
        } else {
            $(this).parent().parent().remove();
        }

        e.preventDefault();
    });

</script>

<style>
    #postcoderows .row {
        background-color: white;
    }

</style>
