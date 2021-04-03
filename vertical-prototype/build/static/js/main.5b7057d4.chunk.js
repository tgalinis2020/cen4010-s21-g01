(this.webpackJsonpclient=this.webpackJsonpclient||[]).push([[0],{33:function(t,e,n){"use strict";n.r(e);var i=n(0),a=n.n(i),r=n(18),s=n.n(r),c=n(6),u=n(21),o=n(10),h=n(20),l=n(8),d=n(19),j=n(14),b=n(13),p=n(11),O=n(12),f=n(23),y=n(22);function m(t,e){var n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null,i="https://lamp.cse.fau.edu/~cen4010_s21_g01/api-v1.php".concat(e),a={method:t,headers:{"Content-Type":"application/json",Accept:"application/json"}};return["POST","PUT","PATCH"].includes(t)&&(a.body=JSON.stringify({data:n})),fetch(i,a)}function v(t){var e=t.split(" "),n=Object(c.a)(e,2),i=n[0],a=n[1],r=i.split("-"),s=Object(c.a)(r,3),u=s[0],o=s[1],h=s[2],l=a.split(":"),d=Object(c.a)(l,3),j=d[0],b=d[1],p=d[2];return Date.UTC(h,o,u,j,b,p)}var g=function(t){Object(f.a)(n,t);var e=Object(y.a)(n);function n(){return Object(p.a)(this,n),e.call(this,"users")}return Object(O.a)(n,[{key:"create",value:function(t){var e=this;return m("POST","/".concat(this.type),{type:this.type,attributes:this.attributes}).then((function(n){return e.hydrate(n.data),m("PUT","/passwords/".concat(n.data.id),t)}))}},{key:"updatePassword",value:function(t,e){return m("PATCH","/passwords/".concat(this.id),{current:t,password:e})}},{key:"login",value:function(t){return m("POST","/session",{username:this.getAttribute("username"),password:t})}},{key:"logout",value:function(){return m("DELETE","/session")}},{key:"subscribeTo",value:function(t){return this.updateToManyRelationship("POST","subscriptions",[t])}},{key:"unsubscribeFrom",value:function(t){return this.updateToManyRelationship("DELETE","subscriptions",[t])}},{key:"addFavorite",value:function(t){return this.updateToManyRelationship("POST","favorites",[t])}},{key:"removeFavorite",value:function(t){return this.updateToManyRelationship("DELETE","favorites",[t])}},{key:"like",value:function(t){return this.updateToManyRelationship("POST","liked-posts",[t])}},{key:"unlike",value:function(t){return this.updateToManyRelationship("DELETE","liked-posts",[t])}}]),n}(function(){function t(e){Object(p.a)(this,t),this.id=null,this.type=e,this.attributes={},this.relationships={}}return Object(O.a)(t,[{key:"hydrate",value:function(t){this.id=t.id,this.attributes=t.attributes,"createdAt"in this.attributes&&(this.createdAt=v(this.attributes.createdAt)),this.relationships=t.relationships}},{key:"setAttribute",value:function(t,e){this.attributes[t]=e}},{key:"getAttribute",value:function(t){return this.attributes[t]}},{key:"toResourceIdentifier",value:function(){return{type:this.type,id:this.id}}},{key:"toResource",value:function(){return{type:this.type,id:this.id,attributes:this.attributes}}},{key:"create",value:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,e={type:this.type,attributes:this.attributes};return null!==t&&(e.relationships=t),m("POST","/".concat(this.type),e).then((function(t){return t.data}))}},{key:"update",value:function(){return m("PATCH","/".concat(this.type),{type:this.type,id:this.id,attributes:this.attributes})}},{key:"delete",value:function(){return m("DELETE","/".concat(this.type,"/").concat(this.id))}},{key:"updateRelationship",value:function(t,e,n){return m(t,"/".concat(this.type,"/").concat(this.id,"/relationships/").concat(e),n)}},{key:"updateToManyRelationship",value:function(t,e,n){return this.updateRelationship(t,e,n.map((function(t){return t.toResourceIdentifier()})))}},{key:"updateToOneRelationship",value:function(t,e,n){return this.updateRelationship(t,e,n.toResourceIdentifier())}}]),t}());var x=n(2),T=function(t){return{type:"users",id:t.id,attributes:{username:t.username,firstName:t.firstName,lastName:t.lastName,email:t.email,createdAt:v(t.createdAt)}}};function k(t){var e=t.onLoginSuccess,n=t.onLoginError,a=t.onLogoutSuccess,r=Object(i.useState)(""),s=Object(c.a)(r,2),u=s[0],h=s[1],d=Object(i.useState)(""),j=Object(c.a)(d,2),b=j[0],p=j[1];return Object(x.jsxs)(l.a,{children:[Object(x.jsxs)(l.a.Group,{children:[Object(x.jsx)(l.a.Label,{children:"Username"}),Object(x.jsx)(l.a.Control,{type:"text",placeholder:"Enter username",onChange:function(t){return h(t.target.value)}})]}),Object(x.jsxs)(l.a.Group,{children:[Object(x.jsx)(l.a.Label,{children:"Password"}),Object(x.jsx)(l.a.Control,{type:"password",placeholder:"Enter password",onChange:function(t){return p(t.target.value)}})]}),Object(x.jsx)(o.a,{variant:"primary",onClick:function(){return m("POST","/session",{username:u,password:b}).then((function(t){if(201!==t.status)throw t.status;return t})).then((function(t){return t.data})).then(T).then(e).catch(n)},children:"Login"}),Object(x.jsx)(o.a,{variant:"secondary",onClick:function(){return m("DELETE","/session").then(a)},children:"Logout"})]})}function E(t){var e=t.image;return Object(x.jsxs)(d.a,{children:[Object(x.jsx)(j.a,{src:e,width:400}),Object(x.jsx)(b.a,{children:"Uploaded Image"})]})}function S(){var t=Object(i.useState)(null),e=Object(c.a)(t,2),n=e[0],a=e[1],r=Object(i.useState)([]),s=Object(c.a)(r,2),d=s[0],j=s[1],b=Object(i.useState)(null),p=Object(c.a)(b,2),O=p[0],f=p[1],y=Object(i.useState)(null),v=Object(c.a)(y,2),S=v[0],w=v[1],A=function(){return m("GET","/users").then((function(t){return t.json()})).then((function(t){return t.data})).then((function(t){return t.map((function(t){var e=new g;return e.hydrate(t),e}))})).then(j)};return m("GET","/session").then((function(t){return t.json()})).then((function(t){return t.data})).then(T).then(w).catch((function(t){return console.log("Not logged in")})),A(),Object(x.jsxs)(h.a,{children:[Object(x.jsx)("h1",{children:"Users and Authentication"}),Object(x.jsx)(o.a,{className:"mb-4",onClick:A,children:"Refresh"}),Object(x.jsxs)(u.a,{children:[Object(x.jsx)("thead",{children:Object(x.jsxs)("tr",{children:[Object(x.jsx)("th",{children:"ID"}),Object(x.jsx)("th",{children:"Username"}),Object(x.jsx)("th",{children:"E-mail Address"}),Object(x.jsx)("th",{children:"First Name"}),Object(x.jsx)("th",{children:"Last Name"}),Object(x.jsx)("th",{children:"Joined On"})]})}),Object(x.jsx)("tbody",{children:d.map((function(t,e){return Object(x.jsxs)("tr",{children:[Object(x.jsx)("td",{children:t.id}),Object(x.jsx)("td",{children:t.getAttribute("username")}),Object(x.jsx)("td",{children:t.getAttribute("email")}),Object(x.jsx)("td",{children:t.getAttribute("firstName")}),Object(x.jsx)("td",{children:t.getAttribute("lastName")}),Object(x.jsx)("td",{children:t.getAttribute("createdAt")})]},e)}))})]}),Object(x.jsxs)("p",{children:["Logged in as: ",S?"".concat(S.attributes.firstName," ").concat(S.attributes.lastName):"(unauthenticated)"]}),Object(x.jsx)(k,{onLoginSuccess:w,onLoginError:function(t){return window.alert("Can't log in! (error code ".concat(t,")"))},onLogoutSuccess:function(){return w(null)}}),Object(x.jsx)("hr",{}),Object(x.jsx)("h1",{children:"File Upload"}),Object(x.jsx)("p",{children:"Note: you must be authenticated to upload images!"}),O&&Object(x.jsx)(E,{image:O}),Object(x.jsxs)(l.a,{children:[Object(x.jsx)(l.a.File,{label:"Upload an image",onChange:function(t){return a(t.target)}}),Object(x.jsx)(o.a,{variant:"primary",onClick:function(){return function(t){return fetch("https://lamp.cse.fau.edu/~cen4010_s21_g01/uploads",{method:"POST",body:t})}(n).then((function(t){return t.json()})).then((function(t){return t.data})).then((function(t){f(t),window.alert("File uploaded! Path: ".concat(t))})).catch((function(t){return window.alert("Unable to upload the image. ".concat(null===S?"You are not signed in!":"Go bug Tom about this."))}))},children:"Upload Image"})]})]})}n(32);s.a.render(Object(x.jsx)(a.a.StrictMode,{children:Object(x.jsx)(S,{})}),document.getElementById("root"))}},[[33,1,2]]]);
//# sourceMappingURL=main.5b7057d4.chunk.js.map