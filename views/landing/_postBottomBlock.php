<div class="search-results1">
    <h4><?php echo CHtml::link(CHtml::encode($data->title), $data->url); ?></h4>
    <div>
        <p class="description">
		<?php
//			$this->beginWidget('CMarkdown', array('purifyOutput'=>true));
			echo $data->content;
//			$this->endWidget();
		?>
        </p>
    </div>
</div>
