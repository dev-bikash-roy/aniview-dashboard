<?php
/** @var array $sources */
?>
<table class="widefat">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Type</th>
            <th>Active</th>
            <th>Last Run</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if ( ! empty( $sources ) ) : ?>
        <?php foreach ( $sources as $src ) : ?>
            <tr>
                <td><?php echo esc_html( $src->id ); ?></td>
                <td><?php echo esc_html( $src->name ); ?></td>
                <td><?php echo esc_html( $src->type ); ?></td>
                <td><?php echo $src->is_active ? 'Yes' : 'No'; ?></td>
                <td><?php echo esc_html( $src->last_run ); ?></td>
                <td>
                    <button>Edit</button>
                    <button>Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else : ?>
        <tr><td colspan="6">No data sources found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
<p><button>Add New</button></p>
