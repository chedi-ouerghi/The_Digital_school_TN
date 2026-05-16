<template>
  <div class="avatar-container">
    <div class="avatar-wrapper">
      <img :src="avatarUrl" alt="Avatar" class="avatar-image" />
      <span class="badge" style="background-color: white;">{{ badge }}</span>
    </div>
 <p class="avatar-username" @click="toggleUsername">
      <span v-if="usernameState === 0">{{ username }}</span>
      <span v-else-if="usernameState === 1">chedi</span>
      <span v-else>fares</span>
    </p>

  </div>
</template>

<script>
export default {
  name: 'Avatar',
  props: {
    avatarUrl: {
      type: String,
      required: true,
    },
    username: {
      type: String,
      required: true,
    },
    badge: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      usernameState: 0,
    };
  },
  methods: {
    toggleUsername() {
      this.usernameState = (this.usernameState + 1) % 3;
    },
  },
}
</script>



 <style scoped>
.avatar-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  background-color: #f9f9f9;
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.avatar-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.avatar-image {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  border: 2px solid #e0e0e0;
}

.badge {
  position: absolute;
  top: 80%;;
  right: 10px;
  background-color: #4caf50; 
  color: white;
  padding: 10px 10px;
  border-radius: 20px;
  font-weight: bold;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.avatar-username {
  margin-top: 10px;
  font-size: 1.2em;
  font-weight: bold;
  color: #333;
}
</style>
