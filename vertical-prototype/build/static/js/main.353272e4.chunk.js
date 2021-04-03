(this.webpackJsonpclient=this.webpackJsonpclient||[]).push([[0],{33:function(t,e,n){"use strict";n.r(e);var i=n(0),a=n.n(i),r=n(18),s=n.n(r),c=n(6),u=n(21),o=n(14),h=n(20),l=n(8),d=n(19),j=n(13),b=n(12),p=n(10),O=n(11),f=n(23),y=n(22);function m(t,e){var n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null,i="https://lamp.cse.fau.edu/~cen4010_s21_g01/api-v1.php".concat(e),a={method:t,headers:{"Content-Type":"application/json",Accept:"application/json"}};return["POST","PUT","PATCH"].includes(t)&&(a.body=JSON.stringify(n)),fetch(i,a).then((function(t){return t.json()}))}function v(t){var e=t.split(" "),n=Object(c.a)(e,2),i=n[0],a=n[1],r=i.split("-"),s=Object(c.a)(r,3),u=s[0],o=s[1],h=s[2],l=a.split(":"),d=Object(c.a)(l,3),j=d[0],b=d[1],p=d[2];return Date.UTC(h,o,u,j,b,p)}var x=function(t){Object(f.a)(n,t);var e=Object(y.a)(n);function n(){return Object(p.a)(this,n),e.call(this,"users")}return Object(O.a)(n,[{key:"create",value:function(t){var e=this;return m("POST","/".concat(this.type),{type:this.type,attributes:this.attributes}).then((function(n){return e.hydrate(n.data),m("PUT","/passwords/".concat(n.data.id),t)}))}},{key:"updatePassword",value:function(t,e){return m("PATCH","/passwords/".concat(this.id),{current:t,password:e})}},{key:"login",value:function(t){return m("POST","/session",{username:this.getAttribute("username"),password:t})}},{key:"logout",value:function(){return m("DELETE","/session")}},{key:"subscribeTo",value:function(t){return this.updateToManyRelationship("POST","subscriptions",[t])}},{key:"unsubscribeFrom",value:function(t){return this.updateToManyRelationship("DELETE","subscriptions",[t])}},{key:"addFavorite",value:function(t){return this.updateToManyRelationship("POST","favorites",[t])}},{key:"removeFavorite",value:function(t){return this.updateToManyRelationship("DELETE","favorites",[t])}},{key:"like",value:function(t){return this.updateToManyRelationship("POST","liked-posts",[t])}},{key:"unlike",value:function(t){return this.updateToManyRelationship("DELETE","liked-posts",[t])}}]),n}(function(){function t(e){Object(p.a)(this,t),this.id=null,this.type=e,this.attributes={},this.relationships={}}return Object(O.a)(t,[{key:"hydrate",value:function(t){this.id=t.id,this.attributes=t.attributes,"createdAt"in this.attributes&&(this.createdAt=v(this.attributes.createdAt)),this.relationships=t.relationships}},{key:"setAttribute",value:function(t,e){this.attributes[t]=e}},{key:"getAttribute",value:function(t){return this.attributes[t]}},{key:"toResourceIdentifier",value:function(){return{type:this.type,id:this.id}}},{key:"toResource",value:function(){return{type:this.type,id:this.id,attributes:this.attributes}}},{key:"create",value:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,e={type:this.type,attributes:this.attributes};return null!==t&&(e.relationships=t),m("POST","/".concat(this.type),e).then((function(t){return t.data}))}},{key:"update",value:function(){return m("PATCH","/".concat(this.type),{type:this.type,id:this.id,attributes:this.attributes})}},{key:"delete",value:function(){return m("DELETE","/".concat(this.type,"/").concat(this.id))}},{key:"updateRelationship",value:function(t,e,n){return m(t,"/".concat(this.type,"/").concat(this.id,"/relationships/").concat(e),n)}},{key:"updateToManyRelationship",value:function(t,e,n){return this.updateRelationship(t,e,n.map((function(t){return t.toResourceIdentifier()})))}},{key:"updateToOneRelationship",value:function(t,e,n){return this.updateRelationship(t,e,n.toResourceIdentifier())}}]),t}());var g=n(2);function T(){var t=Object(i.useState)(null),e=Object(c.a)(t,2),n=e[0],a=e[1],r=Object(i.useState)([]),s=Object(c.a)(r,2),p=s[0],O=s[1],f=Object(i.useState)(null),y=Object(c.a)(f,2),T=y[0],k=y[1],A=Object(i.useState)(null),E=Object(c.a)(A,2),w=E[0],P=E[1],S=Object(i.useState)(""),R=Object(c.a)(S,2),C=R[0],N=R[1],U=Object(i.useState)(""),L=Object(c.a)(U,2),M=L[0],D=L[1],F=function(t){var e=t.image;return Object(g.jsxs)(d.a,{children:[Object(g.jsx)(j.a,{src:e,width:400}),Object(g.jsx)(b.a,{children:"Uploaded Image"})]})};return Object(g.jsxs)(h.a,{children:[Object(g.jsx)("h1",{children:"Users and Authentication"}),Object(g.jsx)(o.a,{onClick:function(){return m("GET","/users").then((function(t){return t.data})).then((function(t){return t.map((function(t){var e=new x;return e.hydrate(t),e}))})).then(O)},children:"Get Users"}),Object(g.jsxs)(u.a,{children:[Object(g.jsx)("thead",{children:Object(g.jsxs)("tr",{children:[Object(g.jsx)("th",{children:"ID"}),Object(g.jsx)("th",{children:"Username"}),Object(g.jsx)("th",{children:"E-mail Address"}),Object(g.jsx)("th",{children:"First Name"}),Object(g.jsx)("th",{children:"Last Name"}),Object(g.jsx)("th",{children:"Joined On"})]})}),Object(g.jsx)("tbody",{children:p.map((function(t,e){return Object(g.jsxs)("tr",{children:[Object(g.jsx)("td",{children:t.id}),Object(g.jsx)("td",{children:t.getAttribute("username")}),Object(g.jsx)("td",{children:t.getAttribute("email")}),Object(g.jsx)("td",{children:t.getAttribute("firstName")}),Object(g.jsx)("td",{children:t.getAttribute("lastName")}),Object(g.jsx)("td",{children:t.getAttribute("createdAt")})]},e)}))})]}),Object(g.jsxs)("p",{children:["Logged in as: ",w?"".concat(w.attributes.firstName," ").concat(w.attributes.lastName):"(unauthenticated)"]}),Object(g.jsxs)(l.a,{children:[Object(g.jsxs)(l.a.Group,{children:[Object(g.jsx)(l.a.Label,{children:"Username"}),Object(g.jsx)(l.a.Control,{type:"text",placeholder:"Enter username",onChange:function(t){return N(t.target.value)}})]}),Object(g.jsxs)(l.a.Group,{children:[Object(g.jsx)(l.a.Label,{children:"Password"}),Object(g.jsx)(l.a.Control,{type:"password",placeholder:"Enter password",onChange:function(t){return D(t.target.value)}})]}),Object(g.jsx)(o.a,{variant:"primary",onClick:function(){return m("POST","/session",{username:C,password:M}).then((function(t){return t.data})).then((function(t){return{type:"users",id:t.id,attributes:{username:t.username,firstName:t.firstName,lastName:t.lastName,email:t.email,createdAt:v(t.createdAt)}}})).then(P)},children:"Login"})]}),Object(g.jsx)("hr",{}),Object(g.jsx)("h1",{children:"File Upload"}),Object(g.jsx)("p",{children:"Note: you must be authenticated to upload images!"}),T&&Object(g.jsx)(F,{image:T}),Object(g.jsxs)(l.a,{children:[Object(g.jsx)(l.a.File,{label:"Upload an image",onChange:function(t){return a(t.target)}}),Object(g.jsx)(o.a,{variant:"primary",onClick:function(){return function(t){return fetch("https://lamp.cse.fau.edu/~cen4010_s21_g01/uploads",{method:"POST",body:t}).then((function(t){return t.json()})).then((function(t){return t.data}))}(n).then((function(t){k(t),window.alert("File uploaded! Path: ".concat(t))})).catch((function(t){return window.alert("Unable to upload the image. ".concat(null===w?"You are not signed in!":"Go bug Tom about this."))}))},children:"Upload Image"})]})]})}n(32);s.a.render(Object(g.jsx)(a.a.StrictMode,{children:Object(g.jsx)(T,{})}),document.getElementById("root"))}},[[33,1,2]]]);
//# sourceMappingURL=main.353272e4.chunk.js.map