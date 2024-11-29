// Verificar si el usuario ha iniciado sesión
const checkAuth = () => {
  const user = JSON.parse(localStorage.getItem('user'))
  if (!user) {
    window.location.href = '../login/index.html'
    return null
  }
  return user
}

// Obtener las recetas del usuario
const fetchUserRecipes = async userId => {
  try {
    const response = await fetch(`/api/recipes/?user_id=${userId}`, {
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json'
      }
    })

    if (!response.ok) {
      throw new Error('Error al obtener las recetas')
    }

    const recipes = await response.json()
    return recipes
  } catch (error) {
    console.error('Error:', error)
    return []
  }
}

// Eliminar receta
const deleteRecipe = async recipeId => {
  if (!confirm('¿Estás seguro de que deseas eliminar esta receta?')) {
    return
  }

  try {
    const response = await fetch(`/api/recipes/?id=${recipeId}`, {
      method: 'DELETE',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json'
      }
    })

    if (!response.ok) {
      throw new Error('Error al eliminar la receta')
    }

    // Refrescar la página después de la eliminación exitosa
    window.location.reload()
  } catch (error) {
    console.error('Error:', error)
    alert('Error al eliminar la receta')
  }
}

// Crear tarjeta de receta
const createRecipeCard = recipe => {
  return `
        <div class="recipe-card">
            <img src="${recipe.imgurl}" alt="${recipe.name}" class="recipe-card-image">
            <div class="recipe-card-content">
                <h2 class="recipe-card-title">${recipe.name}</h2>
                <div class="recipe-card-actions">
                    <button onclick="window.location.href='/recipes/?id=${recipe.id}'" class="recipe-card-button view-button">
                        Ver receta
                    </button>
                    <button onclick="window.location.href='edit.html?id=${recipe.id}'" class="recipe-card-button edit-button">
                        Editar
                    </button>
                    <button onclick="deleteRecipe(${recipe.id})" class="recipe-card-button delete-button">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    `
}

// Inicializar página
const initializePage = async () => {
  const user = checkAuth()
  if (!user) return

  const recipes = await fetchUserRecipes(user.id)
  const recipesGrid = document.getElementById('recipesGrid')
  const noRecipesMessage = document.getElementById('noRecipesMessage')

  if (recipes.length === 0) {
    noRecipesMessage.style.display = 'block'
    recipesGrid.style.display = 'none'
  } else {
    noRecipesMessage.style.display = 'none'
    recipesGrid.innerHTML = recipes.map(recipe => createRecipeCard(recipe)).join('')
  }

  // Agregar evento al botón de crear receta
  document.getElementById('createRecipeBtn').addEventListener('click', () => {
    window.location.href = 'create.html'
  })
}

// Cargar página cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', initializePage)

// Agregar al final del archivo
document.getElementById('backBtn').addEventListener('click', () => {
  window.history.back()
})
