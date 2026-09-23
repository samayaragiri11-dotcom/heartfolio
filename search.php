<?php 
include 'assets/includes/navbar.php'; 
?>

<div class="container" style="padding: 60px 20px;">
    <div class="section-title">
        <h2>Search</h2>
        <p>Find your perfect magazine</p>
    </div>
    
    <div class="form-container" style="max-width: 600px;">
        <form action="shop.php" method="GET">
            <div class="form-group">
                <label for="search">Search for magazines</label>
                <input type="text" id="search" name="search" placeholder="Enter keywords..." required>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
</div>

<?php include 'assets/includes/footer.php'; ?>
