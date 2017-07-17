/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

document.addEventListener("DOMContentLoaded", function() {
    Typos._init();
});

var Typos = {
  _init: function() {
      this.contextBlock = $("#context-block");
      this.contextBlockText = this.contextBlock.children().first();
  },
  
  /**
   * Показывает окно, содержащее информацию о контексте.
   * @param {type} link Элемент a.typos_context
   * @returns {Boolean}
   */
  handleLink: function(link) {
    var text = link.getAttribute("context");
    var typo = link.getAttribute("typo");
    var correct = link.getAttribute("correct");
    var self = this;
    
    text = text.replace(typo, "<span class='typo'>" + typo + " -> " + correct + "</span>");
    
    self.contextBlockText.html(text);
    self.contextBlock.dialog();
    
    return false;
  } 
};


