import { BasketsAPI } from '/recipee/libs/data/index.js'
import { RecipesAPI } from '/recipee/js/api/index.js'

// Check authentication
const user = JSON.parse(localStorage.getItem('user'))
if (!user) {
  window.location.href = '/recipee/login/'
}

// Set user info
document.getElementById('userName').textContent = user.name
document.getElementById('userInitials').textContent = user.name.charAt(0).toUpperCase()

// Handle logout
document.getElementById('logoutButton').addEventListener('click', e => {
  e.preventDefault()
  localStorage.removeItem('user')
  window.location.href = '/recipee/login/'
})

// Load home data
async function loadhomeData() {
  try {
    // Get baskets count
    const baskets = BasketsAPI.getBaskets()
    document.getElementById('basketsCount').textContent = `${baskets.length} canasta${
      baskets.length !== 1 ? 's' : ''
    } creada${baskets.length !== 1 ? 's' : ''}`

    // Get recipes count
    const recipes = await RecipesAPI.getRecipes()
    document.getElementById('recipesCount').textContent = `${recipes.length} receta${
      recipes.length !== 1 ? 's' : ''
    } encontrada${recipes.length !== 1 ? 's' : ''}`

    // Get favorites (implement this based on your favorites system)
    document.getElementById('favoritesCount').textContent = '0 recetas guardadas'
  } catch (error) {
    console.error('Error loading home data:', error)
  }
}

loadhomeData()
