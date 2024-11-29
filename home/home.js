// Set user info
const user = JSON.parse(localStorage.getItem('user'))
if (user) {
  document.getElementById('userName').textContent = user.name
  document.getElementById('userInitials').textContent = user.name.charAt(0).toUpperCase()

  // Load nav menu
  const navMenu = document.getElementById('navMenu')
  //append "Mis recetas" to the nav menu
  const navItem = document.createElement('li')
  navItem.classList.add('nav-item')
  navItem.innerHTML = '<a href="/my-recipes/" class="nav-link">Mis recetas</a>'
  navMenu.appendChild(navItem)

  // Handle logout
  const logoutButton = document.getElementById('logoutButton')
  logoutButton.addEventListener('click', e => {
    e.preventDefault()
    localStorage.removeItem('user')
    window.location.href = '/login/'
  })
} else {
  document.getElementById('userName').textContent = 'Invitado'
  document.getElementById('userInitials').textContent = 'I'

  // Handle logout
  const logoutButton = document.getElementById('logoutButton')
  logoutButton.innerHTML = `
    <i class="fas fa-sign-out-alt"></i>
    Iniciar sesión`
  logoutButton.addEventListener('click', e => {
    e.preventDefault()
    localStorage.removeItem('user')
    window.location.href = '/login/'
  })
}

// set recipes
const updateRecipes = async () => {
  try {
    const currentItems = JSON.parse(localStorage.getItem('basket')) || []
    const ingredientIds = currentItems.map(item => item.id)
    const searchInput = document.getElementById('searchInput')
    const searchQuery = searchInput.value

    const url = `/api/recipes/index.php?ingredient_ids=${ingredientIds.join(',')}&search=${searchQuery}`

    const res = await fetch(url, {
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json'
      }
    })

    const responseText = await res.text()

    // Try to parse the response as JSON
    let data
    try {
      data = JSON.parse(responseText)
    } catch (parseError) {
      throw new Error('Invalid JSON response from server')
    }

    const recipesGrid = document.getElementById('recipesGrid')
    recipesGrid.innerHTML = ''

    if (data.length === 0) {
      recipesGrid.innerHTML = '<div class="no-results">No se encontraron recetas</div>'
      return
    }

    data.forEach(recipe => {
      const recipeCard = document.createElement('button')
      recipeCard.classList.add('recipe-card')
      recipeCard.setAttribute('data-id', recipe.id)
      recipeCard.addEventListener('click', e => {
        e.preventDefault()
        window.location.href = `/recipes/?id=${recipe.id}`
      })
      recipeCard.innerHTML = `
        <img src="${recipe.imgurl}" alt="${recipe.name}" />
        <div class="recipe-info">
          <h3>${recipe.name}</h3>
        </div>
      `
      recipesGrid.appendChild(recipeCard)
    })
  } catch (error) {
    console.error('Error:', error)
    const recipesGrid = document.getElementById('recipesGrid')
    recipesGrid.innerHTML = '<div class="error">Error al cargar las recetas</div>'
  }
}

// update recipes
updateRecipes([], '')

const updateBasketCard = ingredientItem => {
  // Si hay un nuevo ingrediente, añadirlo al localStorage
  if (ingredientItem) {
    const currentBasket = JSON.parse(localStorage.getItem('basket')) || []
    localStorage.setItem('basket', JSON.stringify([...currentBasket, ingredientItem]))
  }

  // Limpiar el contenido actual de la tarjeta
  const basketCard = document.getElementById('basketCard')
  basketCard.innerHTML = ''

  // Obtener la canasta actual
  const currentItems = JSON.parse(localStorage.getItem('basket')) || []
  updateRecipes()

  // Mostrar cada ingrediente
  currentItems.forEach(item => {
    const basketItem = document.createElement('li')
    basketItem.classList.add('basket-item')
    basketItem.innerHTML = `
      <h3>${item.name}</h3>
      <button class="remove-button" data-id="${item.id}">
        <i class="fas fa-trash-alt"></i>
      </button>
    `
    basketCard.appendChild(basketItem)

    // Handle remove ingredient
    const removeButton = basketItem.querySelector('.remove-button')
    removeButton.addEventListener('click', e => {
      e.preventDefault()
      const itemId = e.currentTarget.dataset.id
      // Obtener el estado actual del localStorage
      const currentBasket = JSON.parse(localStorage.getItem('basket')) || []
      // Filtrar el elemento
      const updatedItems = currentBasket.filter(b => b.id !== parseInt(itemId))
      // Actualizar localStorage
      localStorage.setItem('basket', JSON.stringify(updatedItems))
      updateRecipes()
      // Actualizar la vista
      updateBasketCard()
    })
  })
}

// Inicializar la tarjeta
updateBasketCard()

// handle basket form
const basketForm = document.getElementById('basketForm')
basketForm.addEventListener('submit', async e => {
  e.preventDefault()
  const ingredient = e.target.elements.ingredient.value
  if (!ingredient.trim()) return

  const response = await fetch(`/api/ingredients/?name=${ingredient.trim()}`)
  const data = (await response.json())[0]
  if (!data) return
  const ingredientItem = {
    id: data.id,
    name: data.name
  }

  updateBasketCard(ingredientItem)
  e.target.reset() // Limpiar el formulario
})

// handle search
const searchForm = document.getElementById('searchForm')
searchForm.addEventListener('submit', e => {
  e.preventDefault()
  updateRecipes()
})
