<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  initialState: {
    type: Object,
    required: true
  }
});

const filter = ref('All categories');
const draftTitle = ref('');
const draftBody = ref('');
const isSubmitting = ref(false);
const errorMessage = ref('');

// Authentication states
const currentUser = ref(props.initialState.currentUser || null);
const showLoginModal = ref(false);
const loginEmail = ref('');
const loginPassword = ref('');
const loginError = ref('');
const isLoggingIn = ref(false);

const categories = ref(props.initialState.categories || []);
const topics = ref(props.initialState.topics || []);
const stats = ref(props.initialState.stats || []);
const guidelines = ref(props.initialState.guidelines || []);

const filteredTopics = computed(() => {
  if (filter.value === 'All categories') {
    return topics.value;
  }
  return topics.value.filter((topic) => topic.category === filter.value);
});

function badgeClass(topic) {
  return {
    'forums-badge': true,
    'forums-badge-green': topic.category === 'Framework Help',
    'forums-badge-violet': topic.category === 'Announcements',
    'forums-badge-red': topic.category === 'Security'
  };
}

function submitLogin() {
  const email = loginEmail.value.trim();
  const password = loginPassword.value.trim();

  if (email === '' || password === '') {
    loginError.value = 'Please provide both email and password.';
    return;
  }

  isLoggingIn.value = true;
  loginError.value = '';

  const params = new URLSearchParams();
  params.append('email', email);
  params.append('password', password);

  fetch('/forums/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: params
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Invalid email or password credentials.');
    }
    return response.json();
  })
  .then(user => {
    currentUser.value = user;
    showLoginModal.value = false;
    loginEmail.value = '';
    loginPassword.value = '';
  })
  .catch(error => {
    loginError.value = error.message;
  })
  .finally(() => {
    isLoggingIn.value = false;
  });
}

function submitLogout() {
  fetch('/forums/logout', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    }
  })
  .then(response => {
    if (response.ok) {
      currentUser.value = null;
    }
  });
}

function createDraft() {
  if (!currentUser.value) {
    showLoginModal.value = true;
    return;
  }

  const title = draftTitle.value.trim();
  const body = draftBody.value.trim();
  const category = filter.value === 'All categories' ? 'Framework Help' : filter.value;

  if (title === '' || body === '') {
    errorMessage.value = 'Please provide both a title and details for your topic.';
    return;
  }

  isSubmitting.value = true;
  errorMessage.value = '';

  const params = new URLSearchParams();
  params.append('title', title);
  params.append('body', body);
  params.append('category', category);

  fetch('/forums/topics', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: params
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Failed to create topic. Please try again.');
    }
    return response.json();
  })
  .then(newTopic => {
    // Add the new topic to the start of the list
    topics.value.unshift(newTopic);
    
    // Increment the category topic count dynamically!
    const cat = categories.value.find(c => c.name === newTopic.category);
    if (cat) {
      cat.count++;
    }
    
    // Increment the total topics stat!
    const topicsStat = stats.value.find(s => s.label === 'Topics');
    if (topicsStat) {
      topicsStat.value = (parseInt(topicsStat.value.replace(/,/g, '')) + 1).toLocaleString();
    }

    // Reset inputs
    draftTitle.value = '';
    draftBody.value = '';
    
    // Smooth scroll to top of latest activity
    const boardHead = document.querySelector('.forums-board-head');
    if (boardHead) {
      boardHead.scrollIntoView({ behavior: 'smooth' });
    }
  })
  .catch(error => {
    errorMessage.value = error.message;
  })
  .finally(() => {
    isSubmitting.value = false;
  });
}
</script>

<template>
  <header class="forums-topbar">
    <a class="forums-brand" href="/">
      <img :src="'/favicon.svg'" alt="WTD Core">
      <span>WTD Forums</span>
    </a>
    <nav aria-label="Forums navigation" class="forums-nav-list">
      <a href="/">Home</a>
      <a href="/forums" class="active">Forums</a>
      <a href="/docs/api">API Docs</a>
      <a href="/health">Health</a>
      <div class="forums-user-menu" v-if="currentUser">
        <span class="forums-user-name">👤 {{ currentUser.name }}</span>
        <button @click="submitLogout" class="forums-logout-btn" type="button">Logout</button>
      </div>
      <button v-else @click="showLoginModal = true" class="forums-login-btn" type="button">Login</button>
    </nav>
  </header>

  <main class="forums-shell">
    <aside class="forums-sidebar">
      <section class="forums-panel forums-intro" aria-labelledby="forums-title">
        <p class="forums-eyebrow">Open source discussion</p>
        <h1 id="forums-title">Forums</h1>
        <p>Discuss framework usage, package ideas, release workflows, and implementation questions.</p>
        <a class="forums-primary" :href="currentUser ? '#new-topic' : '#'" @click.prevent="!currentUser ? (showLoginModal = true) : null">New topic</a>
      </section>

      <section class="forums-panel" aria-labelledby="forums-categories-title">
        <h2 id="forums-categories-title">Categories</h2>
        <button
          type="button"
          class="forums-category"
          :class="{ 'forums-category-active': filter === 'All categories' }"
          @click="filter = 'All categories'"
        >
          <span>All categories</span>
          <strong>{{ topics.length }}</strong>
        </button>
        <button
          v-for="category in categories"
          :key="category.name"
          type="button"
          class="forums-category"
          :class="{ 'forums-category-active': filter === category.name }"
          @click="filter = category.name"
        >
          <span>{{ category.name }}</span>
          <strong>{{ category.count }}</strong>
        </button>
      </section>

      <section class="forums-panel forums-guidelines" aria-labelledby="forums-guidelines-title">
        <h2 id="forums-guidelines-title">Guidelines</h2>
        <ul>
          <li v-for="guideline in guidelines" :key="guideline">{{ guideline }}</li>
        </ul>
      </section>
    </aside>

    <section class="forums-content">
      <div class="forums-stats" aria-label="Forum statistics">
        <article v-for="item in stats" :key="item.label">
          <strong>{{ item.value }}</strong>
          <span>{{ item.label }}</span>
        </article>
      </div>

      <section class="forums-board" aria-label="Forum topics">
        <div class="forums-board-head">
          <div>
            <p class="forums-eyebrow">Latest activity</p>
            <h2>Community topics</h2>
          </div>
          <label>
            <span>Filter</span>
            <select v-model="filter" aria-label="Filter forum topics">
              <option>All categories</option>
              <option v-for="category in categories" :key="category.name">{{ category.name }}</option>
            </select>
          </label>
        </div>

        <div class="forums-topic-list">
          <div v-if="filteredTopics.length === 0" class="forums-no-topics">
            No topics found in this category.
          </div>
          <article v-for="topic in filteredTopics" :key="topic.title" class="forums-topic">
            <div class="forums-topic-main">
              <span :class="badgeClass(topic)">{{ topic.category }}</span>
              <h3>{{ topic.title }}</h3>
              <p>{{ topic.author }} updated this topic {{ topic.updated }}.</p>
            </div>
            <dl>
              <div><dt>Replies</dt><dd>{{ topic.replies }}</dd></div>
              <div><dt>Views</dt><dd>{{ topic.views }}</dd></div>
              <div><dt>Status</dt><dd>{{ topic.status }}</dd></div>
            </dl>
          </article>
        </div>
      </section>

      <!-- Composer Panel conditionally rendered -->
      <section v-if="currentUser" id="new-topic" class="forums-composer" aria-labelledby="forums-composer-title">
        <div>
          <p class="forums-eyebrow">Draft topic</p>
          <h2 id="forums-composer-title">Start a useful discussion</h2>
        </div>
        <form @submit.prevent="createDraft">
          <input v-model="draftTitle" type="text" placeholder="Topic title" aria-label="Topic title" :disabled="isSubmitting" required>
          <textarea v-model="draftBody" placeholder="Describe the question, decision, or proposal." aria-label="Topic body" :disabled="isSubmitting" required></textarea>
          <div v-if="errorMessage" class="forums-error-box">{{ errorMessage }}</div>
          <button type="submit" :disabled="isSubmitting">
            <span v-if="isSubmitting">Creating topic...</span>
            <span v-else>Create topic</span>
          </button>
        </form>
      </section>

      <!-- Sign In CTA Panel if logged out -->
      <section v-else class="forums-composer forums-login-prompt" id="new-topic">
        <div class="forums-prompt-card">
          <span class="forums-lock-icon">🔒</span>
          <h3>Join the Discussion</h3>
          <p>You must be signed in to create a new discussion thread or post replies.</p>
          <button type="button" class="forums-primary" @click="showLoginModal = true">Sign In to Forums</button>
        </div>
      </section>
    </section>
  </main>

  <!-- Login Modal Overlay -->
  <div v-if="showLoginModal" class="forums-modal-overlay" @click.self="showLoginModal = false">
    <div class="forums-modal-card">
      <div class="forums-modal-header">
        <h3>Sign In to WTD Forums</h3>
        <button type="button" class="forums-modal-close" @click="showLoginModal = false">×</button>
      </div>
      <form @submit.prevent="submitLogin" class="forums-modal-form">
        <p class="forums-modal-desc">Log in using seeded credentials: <code>admin@example.test</code> / <code>password</code></p>
        
        <label class="forums-modal-field">
          <span>Email Address</span>
          <input v-model="loginEmail" type="email" placeholder="admin@example.test" required :disabled="isLoggingIn">
        </label>
        
        <label class="forums-modal-field">
          <span>Password</span>
          <input v-model="loginPassword" type="password" placeholder="••••••••" required :disabled="isLoggingIn">
        </label>
        
        <div v-if="loginError" class="forums-modal-error">{{ loginError }}</div>
        
        <button type="submit" class="forums-primary" :disabled="isLoggingIn">
          <span v-if="isLoggingIn">Signing In...</span>
          <span v-else>Sign In</span>
        </button>
      </form>
    </div>
  </div>
</template>
