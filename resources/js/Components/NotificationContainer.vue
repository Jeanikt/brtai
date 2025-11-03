<template>
    <div class="fixed top-4 right-4 z-50 space-y-3">
        <Notification v-for="notification in notifications" :key="notification.id" :type="notification.type"
            :title="notification.title" :message="notification.message" :duration="notification.duration"
            @close="removeNotification(notification.id)" />
    </div>
</template>

<script setup>
import { ref } from 'vue'
import Notification from './Notification.vue'

const notifications = ref([])

let nextId = 1

const addNotification = (notification) => {
    const id = nextId++
    notifications.value.push({
        id,
        type: notification.type || 'info',
        title: notification.title,
        message: notification.message,
        duration: notification.duration || 5000
    })
}

const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index !== -1) {
        notifications.value.splice(index, 1)
    }
}

defineExpose({
    addNotification,
    removeNotification
})
</script>
