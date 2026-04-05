<?php 
$page_title = "Africa United - Notation"; 
include 'includes/header.php'; 
?>

    <main class="auth-page">
        <div class="form-container">
            <h2>Votre avis compte !</h2>
            <p class="notation-subtitle">
                Merci d'avoir commandé chez Africa United. Notez votre expérience ci-dessous.
            </p>

            <form action="#">
                <div class="rating-group">
                    <label>Qualité des produits</label>
                    <div class="stars">
                        <input type="radio" name="food-rating" id="food-5" value="5"><label for="food-5">★</label>
                        <input type="radio" name="food-rating" id="food-4" value="4"><label for="food-4">★</label>
                        <input type="radio" name="food-rating" id="food-3" value="3"><label for="food-3">★</label>
                        <input type="radio" name="food-rating" id="food-2" value="2"><label for="food-2">★</label>
                        <input type="radio" name="food-rating" id="food-1" value="1"><label for="food-1">★</label>
                    </div>
                </div>

                <div class="rating-group">
                    <label>Qualité de la livraison</label>
                    <div class="stars">
                        <input type="radio" name="delivery-rating" id="delivery-5" value="5"><label for="delivery-5">★</label>
                        <input type="radio" name="delivery-rating" id="delivery-4" value="4"><label for="delivery-4">★</label>
                        <input type="radio" name="delivery-rating" id="delivery-3" value="3"><label for="delivery-3">★</label>
                        <input type="radio" name="delivery-rating" id="delivery-2" value="2"><label for="delivery-2">★</label>
                        <input type="radio" name="delivery-rating" id="delivery-1" value="1"><label for="delivery-1">★</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="comments">Un commentaire particulier ?</label>
                    <textarea id="comments" rows="4" placeholder="Dites-nous ce que vous avez aimé ou ce que nous pouvons améliorer..." class="notation-textarea"></textarea>
                </div>

                <button type="submit" class="btn-submit">Envoyer mon avis</button>
            </form>
        </div>
    </main>

<?php 
include 'includes/footer.php'; 
?>