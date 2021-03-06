<?php
use yii\grid\GridView;
?>
<div class="tag-index">
    <div>
        <h1>Tags Management</h1>
        <p class="lead">Create, View, Update, and Delete Tags.</p>
    </div>
    <div class="body-content">
        <div class="row">
            <p><a href="<?= $createTagUrl ?>">Create a new Tag</a></p>
        </div>
        <div>
            <?php echo(
                GridView::widget([
                    'dataProvider' => $tagsDataProvider,
                    'columns'      => [
                        'id',
                        'name',
                        'shortname',
                        [
                            'label' => 'Created',
                            'class' => 'yii\grid\DataColumn',
                            'value' => function($data) {
                                return date('Y-m-d H:i:s', $data->created_at);
                            }
                        ],
                        [
                            'label' => 'Updated',
                            'class' => 'yii\grid\DataColumn',
                            'value' => function($data) {
                                return date('Y-m-d H:i:s', $data->updated_at);
                            }
                        ],
                        ['class' => 'yii\grid\ActionColumn']
                    ]
                ])
            ); ?>
        </div>
    </div>
</div>