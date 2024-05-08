import { ref, watch } from '@vue/composition-api'
import store from '@/store'
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default function useContactList() {

  const toast = useToast()
  const contactsSearch = ref([])
  const searchQuery = ref('')

  // Vigia o campo de pesquisa (se foi digitado algo nele)
  watch(searchQuery, () => {
    fetchContacts()
  })

  // Traz todos os contatos
  const fetchContacts = (ctx, callback) => {
    store
      .dispatch('app-service/fetchContacts', {
        q: searchQuery.value,
      })
      .then(response => {
        contactsSearch.value = response.data.contacts
      })
  }

  // Adiciona um novo contato
  const addContact = (contactData) => {
    store.dispatch('app-service/addContact', contactData)
      .then(response => {
        fetchContacts()
        toast({
          component: ToastificationContent,
          props: {
            title: response.data.message? response.data.message : 'Contato adicionado com sucesso!',
            icon: 'CheckIcon',
            variant: response.data.message? 'danger' : 'success',
          },
        })
      })
      .catch(() => {
        toast({
          component: ToastificationContent,
          props: {
            title: 'Não foi possível salvar o contato',
            icon: 'AlertTriangleIcon',
            variant: 'danger',
          },
        })
      })
  }
  
  return {
    fetchContacts,
    searchQuery,
    contactsSearch,
    addContact
  }
}
