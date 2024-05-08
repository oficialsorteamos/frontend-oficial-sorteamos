import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const userLocal = ref(JSON.parse(JSON.stringify(props.user.value)))
  
  const resetUserLocal = () => {
    userLocal.value = JSON.parse(JSON.stringify(props.user.value))
  }
  watch(props.user, () => {
    resetUserLocal()
  })

  const onSubmit = () => {
    const userData = JSON.parse(JSON.stringify(userLocal))
    //Manda os dados para serem processados
    emit('update-user-access', userData.value)
  }

  return {
    userLocal,

    resetUserLocal,

    onSubmit,
  }
}
