import { ref, watch } from '@vue/composition-api'
// import store from '@/store'

export default function useAddressHandler(props, emit) {
  // ------------------------------------------------
  // taskLocal
  // ------------------------------------------------
  const socialLocal = ref(JSON.parse(JSON.stringify(props.social.value)))
  const resetAddressLocal = () => {
    socialLocal.value = JSON.parse(JSON.stringify(props.social.value))
  }
  watch(props.social, () => {
    resetAddressLocal()
  })

  // ------------------------------------------------
  // isEventHandlerSidebarActive
  // * Clear form if sidebar is closed
  // ! We can hide it using @hidden event
  // ------------------------------------------------
  // watch(props.isEventHandlerSidebarActive, val => {
  //   // ? Don't reset event till transition is finished
  //   if (!val) {
  //     setTimeout(() => {
  //       clearForm.value()
  //     }, 350)
  //   }
  // })

  const onSubmit = () => {
    const socialData = JSON.parse(JSON.stringify(socialLocal))

    // * If event has id => Edit Event
    // Emit event for add/update event
    if (props.social.value.id) emit('update-social-network', socialData.value)
    else emit('add-social-network', socialData.value)

    // Close sidebar
    emit('update:is-task-handler-sidebar-active', false)
  }

  // *===============================================---*
  // *--------- UI ---------------------------------------*
  // *===============================================---*

  // ------------------------------------------------
  // guestOptions
  // ------------------------------------------------

  /* eslint-disable global-require */
  const assigneeOptions = [
    { avatar: require('@/assets/images/avatars/1-small.png'), fullName: 'Jane Foster' },
    { avatar: require('@/assets/images/avatars/3-small.png'), fullName: 'Donna Frank' },
    { avatar: require('@/assets/images/avatars/5-small.png'), fullName: 'Gabrielle Robertson' },
    { avatar: require('@/assets/images/avatars/7-small.png'), fullName: 'Lori Spears' },
    { avatar: require('@/assets/images/avatars/9-small.png'), fullName: 'Sandy Vega' },
    { avatar: require('@/assets/images/avatars/11-small.png'), fullName: 'Cheryl May' },
  ]
  /* eslint-enable global-require */

  const resolveAvatarVariant = tags => {
    if (tags.includes('high')) return 'primary'
    if (tags.includes('medium')) return 'warning'
    if (tags.includes('low')) return 'success'
    if (tags.includes('update')) return 'danger'
    if (tags.includes('team')) return 'info'
    return 'primary'
  }

  const tagOptions = [
    { label: 'Team', value: 'team' },
    { label: 'Low', value: 'low' },
    { label: 'Medium', value: 'medium' },
    { label: 'High', value: 'high' },
    { label: 'Update', value: 'update' },
  ]

  return {
    socialLocal,
    resetAddressLocal,

    // UI
    assigneeOptions,
    resolveAvatarVariant,
    tagOptions,
    onSubmit,
  }
}
