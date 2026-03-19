<!-- widget content -->
<div class="widget-body no-padding mobile-wrapper">

    <div class="widget-body-toolbar">
        <h2>
            <?php echo $table_header?>
        </h2>
    </div>
    <div class="table-wrapper">
    <table data-id="1" class="table table-striped table-bordered table-hover table-recent-search-results">
        <thead>
        <tr>
            <th>Value</th>
            <th>Address</th>
            <th>Status</th>
            <th>List Price</th>
            <th>Sq. Ft.</th>
            <th>Beds/Baths</th>
            <th>List Date</th>
        </tr>
        </thead>
        <tbody>

        <?php foreach($property_models as $property_model): ?>

            <tr>
                <td><?php // echo $this->render('@app/views/property/_property_image_block', ['property_model' => $property_model])?></td>
                <td><?php // echo $this->render('@app/views/property/_property_address_block', ['property_model' => $property_model])?></td>
                <td><?php // echo $this->render('@app/views/property/_property_status_mark', ['property_model' => $property_model])?></td>
                <td><?php // echo $this->render('@app/views/property/_property_price_block', ['property_model' => $property_model])?></td>
                <td><?php // echo $this->render('@app/views/property/_property_sqft_block', ['property_model' => $property_model])?></td>
                <td><?php // echo $this->render('@app/views/property/_property_bedbath_block', ['property_model' => $property_model])?></td>
                <td><?php // echo $this->render('@app/views/property/_property_date_block', ['property_model' => $property_model])?></td>
            </tr>

        <?php endforeach; ?>

        </tbody>
    </table>
    </div>
</div>

<div style="padding: 10px 0;">&nbsp;</div>