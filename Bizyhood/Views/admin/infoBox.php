<?php if(count($zones) > 0): ?>
    <p>Here is a list of the zones you have registered in Bizyhood. If you
    would like to embed a zone right into the post, paste in a shortcode:</p>
    <table>
        <thead>
            <tr>
                <th>Zone Name</th>
                <th>Shortcode</th>
            </tr>            
        </thead>
        <tbody>
            <?php foreach($zones as $id => $zone): ?>
            <tr>
                <td><?php echo htmlentities($zone->name) ?></td>
                <td><strong>[bizyhood zone="<?php echo $zone->id ?>"]</strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
        <p style="color: green; font-weight: bold;">You either have no zones or
            Bizyhood isn't configured correctly. Go to 'Settings', then 'Bizyhood',
        and make sure your access token is correct, and make sure you have zones set up.</p>
<?php endif; ?>
