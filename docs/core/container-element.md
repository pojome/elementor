# Container Element

Elementor's new replacement for Sections & Columns.

Container uses Flex (in the future also Grid) and gives you the option to drag & drop Anything. Anywhere. Even nested!

## Product Knowledge Base:

- [A Complete Guide to Flexbox](https://css-tricks.com/snippets/css/a-guide-to-flexbox/)


- [Basic concepts of flexbox](https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Flexible_Box_Layout/Basic_Concepts_of_Flexbox)


- [Flexbox Froggy - A game for learning CSS flexbox](https://flexboxfroggy.com/)


- [Flex Cheatsheet](https://yoksel.github.io/flex-cheatsheet/)



## Attention Needed / Known Issues:

Creating a new core element in a legacy code-base is never easy. Therefore, we had to deal with many weird problems.

The main one is the DnD (Drag & Drop) aspect. DnD-ing into a `flex container` is a real challenge by itself, and when you add the fact that you need to support the old existing DnD mechanism, it becomes a huge headache.

Some things that we needed to take into consideration were:

- What are you gonna do when the `flex-direction` is `row`?
- How jQuery UI Draggable & Sortable are gonna treat dragging into a `flex container`?
- How it will affect the existing placeholder mechanism that knows only about things like `top` / `bottom` / `vertical`?
- How does it affect the UX/UI?
- Will it cause issues due to `flex-basis` / `flex-shrink` / `flex-grow`?
- How do you even resize a `flex item` if there are some calculations like grow/shrink?
- How are you gonna drag a Container next to another Container without triggering a `dragover` event on the existing Container?

The answer to all those question is one - Yes. It caused any problem you can imagine. And even more.
We had to implement a lot of workarounds. From simple UI tweaks, To hooking into jQuery UI Draggable events and overriding some of its behaviors.

Another aspect is the new elements hierarchy. We needed to decide which elements we want to allow inside Containers. The final decision (which actually was fairly easy) is to allow only Widgets & Containers inside a Container, since Sections & Columns are useless there. Plus, we decided to also allow Container inside Column, in order to let users with existing websites to use this new futuristic feature.
