import { timestamp } from '@vueuse/core'
import { defineStore } from 'pinia'

export const useBehaviorStore = defineStore('behavior', {
  state: () => ({
    seenChatIntro: false,
    visitedPages: [],
    actionHistory:[],
    chat:'',
  }),
  actions: {
    markChatIntroSeen() {
      this.seenChatIntro = true
    },
    addVisitedPage(page) {
      if (!this.visitedPages.includes(page)) {
        this.visitedPages.push(page)
      }
    },
    logAction(page, action){
      this.actionHistory.push({
        page,
        action,
        timestamp:Date.now()
      })
    },
    updateChat(text){
      this.chat =text
    },
    clearChat(){
      this.chat =''
    }
  },
  persist: true
})
