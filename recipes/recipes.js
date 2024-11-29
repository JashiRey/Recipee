// Get recipe_id from URL search params
const urlParams = new URLSearchParams(window.location.search)
const recipeId = urlParams.get('id')

// Fetch recipe details
const fetchRecipe = async () => {
  try {
    if (!recipeId) {
      throw new Error('Recipe ID is required')
    }

    const response = await fetch(`/api/recipes/?id=${recipeId}`, {
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json'
      }
    })

    if (!response.ok) {
      throw new Error('Recipe not found')
    }

    const data = (await response.json())[0]

    // Assuming data is an array with one recipe
    const recipe = Array.isArray(data) ? data[0] : data

    if (!recipe) {
      throw new Error('Recipe not found')
    }

    // Update DOM with recipe details
    document.getElementById('recipeName').textContent = recipe.name
    document.getElementById('recipeImage').src = recipe.imgurl
    document.getElementById('recipeImage').alt = recipe.name
    document.getElementById('recipeContent').textContent = recipe.content

    // If you have ingredients section
    if (recipe.ingredients) {
      const ingredientsList = document.getElementById('ingredientsList')
      ingredientsList.innerHTML = recipe.ingredients
        .map(
          ingredient => `
          <li class="ingredient-item">
            ${ingredient.quantity} ${ingredient.unit} ${ingredient.name}
          </li>
        `
        )
        .join('')
    }
  } catch (error) {
    console.error('Error:', error)
    document.getElementById('recipeContainer').innerHTML = `
      <div class="error-message">
        ${error.message || 'Error al cargar la receta'}
      </div>
    `
  }
}

// Load recipe when page loads
document.addEventListener('DOMContentLoaded', fetchRecipe)
