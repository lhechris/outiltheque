import {test, expect} from "vitest";
import {mount} from "@vue/test-utils"

import Outil from '../components/Outil.vue'



test("mount component", async () => {
  expect(Outil).toBeTruthy();

  const wrapper = mount(Outil, {
    props: {
      value: {
        "file_path" : "1234", "nom" : "5678", "description" : "Il me faut une description avec plus de cinquante cinq mots" , "duree" : 7, "prix" : 12, "nombre": 5
      },
      link : "/outil/4",
      linkname : "vers truc"
    },

    global: {
        stubs: ['RouterLink'],
      },

  });
  expect(wrapper.text()).toContain("5678Il me faut une description avec plus de cinquante cinq...Durée d'emprunt 7 jours12€5 dispo");
});