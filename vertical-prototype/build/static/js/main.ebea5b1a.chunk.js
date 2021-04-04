(this.webpackJsonpclient=this.webpackJsonpclient||[]).push([[0],{39:function(t,e,n){"use strict";n.r(e);var r=n(0),a=n.n(r),i=n(25),c=n.n(i),s=n(13),u=n(7),o=n(26),l=n(28),h=n(18),j=n(17),d=n(27),b=n(9),f=n(10),p=n(23);var O=function(t,e){var n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null,r="https://lamp.cse.fau.edu/~cen4010_s21_g01/api-v1.php".concat(e),a={method:t,headers:{"Content-Type":"application/json",Accept:"application/json"}};return["POST","PUT","PATCH"].includes(t)&&(a.body=JSON.stringify({data:n})),fetch(r,a)};var x=function(t){var e=t.split(" "),n=Object(u.a)(e,2),r=n[0],a=n[1],i=r.split("-"),c=Object(u.a)(i,3),s=c[0],o=c[1],l=c[2],h=a.split(":"),j=Object(u.a)(h,3),d=j[0],b=j[1],f=j[2];return Date.UTC(l,o,s,d,b,f)},v=n(1);var g=function(t){var e=t.user,n=t.onLogout,r=x(e.getAttribute("createdAt"));return Object(v.jsxs)(p.a,{children:[Object(v.jsx)("img",{width:64,height:64,className:"mr-3",src:e.getAttribute("avatar"),alt:e.getAttribute("username")+"'s avatar"}),Object(v.jsxs)(p.a.Body,{children:[Object(v.jsxs)("h5",{children:[e.getAttribute("firstName")," ",e.getAttribute("lastName")," (",e.getAttribute("username"),")"]}),Object(v.jsxs)("p",{children:["Joined on ",r]}),Object(v.jsx)(f.a,{children:Object(v.jsx)(b.a,{onClick:function(){return O("DELETE","/session").then(n)},children:"Log out"})})]})]})},y=n(6),m=n(11),A=n(12),T=n(16),E=n(15),C=function(){function t(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};Object(m.a)(this,t),this.id=e.id||null,this.attributes=e.attributes||{},this.dirtyAttributes=[],this.relationships=e.relationships||{},"createdAt"in this.attributes&&(this.createdAt=x(this.attributes.createdAt))}return Object(A.a)(t,[{key:"type",get:function(){return"generic"}},{key:"hydrate",value:function(t){return this.id=t.id||null,this.attributes=t.attributes||{},this.relationships=t.relationships||{},"createdAt"in this.attributes&&(this.createdAt=x(this.attributes.createdAt)),this}},{key:"setAttribute",value:function(t,e){return this.dirtyAttributes.push(t),this.attributes[t]=e,this}},{key:"getAttribute",value:function(t){return this.attributes[t]}},{key:"toResourceIdentifier",value:function(){return{type:this.type,id:this.id}}},{key:"toResource",value:function(){return{type:this.type,id:this.id,attributes:this.attributes}}},{key:"create",value:function(){var t,e=this,n=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,r={},a=Object(s.a)(this.dirtyAttributes);try{for(a.s();!(t=a.n()).done;){var i=t.value;r[i]=this.attributes[i]}}catch(u){a.e(u)}finally{a.f()}var c={type:this.type,attributes:r};return null!==n&&(c.relationships=n),O("POST","/".concat(this.type),c).then((function(t){return e.dirtyAttributes=[],t.json()})).then((function(t){return t.data}))}},{key:"update",value:function(){var t,e=this,n={},r=Object(s.a)(this.dirtyAttributes);try{for(r.s();!(t=r.n()).done;){var a=t.value;n[a]=this.attributes[a]}}catch(c){r.e(c)}finally{r.f()}var i={type:this.type,id:this.id,attributes:n};return O("PATCH","/".concat(this.type),i).then((function(){return e.dirtyAttributes=[],e}))}},{key:"delete",value:function(){return O("DELETE","/".concat(this.type,"/").concat(this.id))}},{key:"updateRelationship",value:function(t,e,n){return O(t,"/".concat(this.type,"/").concat(this.id,"/relationships/").concat(e),n)}},{key:"updateToManyRelationship",value:function(t,e,n){return this.updateRelationship(t,e,n.map((function(t){return t.toResourceIdentifier()})))}},{key:"updateToOneRelationship",value:function(t,e,n){return this.updateRelationship(t,e,n.toResourceIdentifier())}}]),t}(),k=function(t){Object(T.a)(n,t);var e=Object(E.a)(n);function n(){return Object(m.a)(this,n),e.apply(this,arguments)}return Object(A.a)(n,[{key:"type",get:function(){return"users"}},{key:"create",value:function(t){var e=this;return O("POST","/".concat(this.type),{type:this.type,attributes:this.attributes}).then((function(n){return e.hydrate(n.data),O("PUT","/passwords/".concat(n.data.id),t)})).then((function(t){return e}))}},{key:"updatePassword",value:function(t,e){return O("PATCH","/passwords/".concat(this.id),{current:t,password:e})}},{key:"login",value:function(t){return O("POST","/session",{username:this.getAttribute("username"),password:t})}},{key:"logout",value:function(){return O("DELETE","/session")}},{key:"subscribeTo",value:function(t){return this.updateToManyRelationship("POST","subscriptions",[t])}},{key:"unsubscribeFrom",value:function(t){return this.updateToManyRelationship("DELETE","subscriptions",[t])}},{key:"addFavorite",value:function(t){return this.updateToManyRelationship("POST","favorites",[t])}},{key:"removeFavorite",value:function(t){return this.updateToManyRelationship("DELETE","favorites",[t])}},{key:"like",value:function(t){return this.updateToManyRelationship("POST","liked-posts",[t])}},{key:"unlike",value:function(t){return this.updateToManyRelationship("DELETE","liked-posts",[t])}}]),n}(C);var S=function(t){var e=new FormData;return e.append("data",t,t.name),fetch("https://lamp.cse.fau.edu/~cen4010_s21_g01/api-v1.php/upload",{method:"POST",body:e})};var w=function(t){var e=t.onRegistered,n=t.onError,a=Object(r.useState)(""),i=Object(u.a)(a,2),c=i[0],s=i[1],o=Object(r.useState)(""),l=Object(u.a)(o,2),h=l[0],j=l[1],d=Object(r.useState)(""),p=Object(u.a)(d,2),x=p[0],g=p[1],m=Object(r.useState)(""),A=Object(u.a)(m,2),T=A[0],E=A[1],C=Object(r.useState)(""),w=Object(u.a)(C,2),P=w[0],L=w[1],R=Object(r.useState)(null),G=Object(u.a)(R,2),N=G[0],D=G[1],F=new k;return Object(v.jsxs)(y.a,{children:[Object(v.jsxs)(y.a.Group,{children:[Object(v.jsx)(y.a.Label,{children:"First Name"}),Object(v.jsx)(y.a.Control,{type:"text",placeholder:"Enter first name",onChange:function(t){return g(t.target.value)}})]}),Object(v.jsxs)(y.a.Group,{children:[Object(v.jsx)(y.a.Label,{children:"Last Name"}),Object(v.jsx)(y.a.Control,{type:"text",placeholder:"Enter last name",onChange:function(t){return E(t.target.value)}})]}),Object(v.jsxs)(y.a.Group,{children:[Object(v.jsx)(y.a.Label,{children:"E-mail Address"}),Object(v.jsx)(y.a.Control,{type:"text",placeholder:"Enter e-mail address",onChange:function(t){return L(t.target.value)}})]}),Object(v.jsxs)(y.a.Group,{children:[Object(v.jsx)(y.a.Label,{children:"Username"}),Object(v.jsx)(y.a.Control,{type:"text",placeholder:"Enter username",onChange:function(t){return s(t.target.value)}})]}),Object(v.jsxs)(y.a.Group,{children:[Object(v.jsx)(y.a.Label,{children:"Password"}),Object(v.jsx)(y.a.Control,{type:"password",placeholder:"Enter password",onChange:function(t){return j(t.target.value)}})]}),Object(v.jsxs)(y.a.Group,{children:[Object(v.jsx)(y.a.Label,{children:"Avatar"}),Object(v.jsx)(y.a.File,{custom:!0,label:"Upload an image",onChange:function(t){return D(t.target.files.item(0))}})]}),Object(v.jsx)(f.a,{children:Object(v.jsx)(b.a,{variant:"primary",onClick:function(){return F.setAttribute("firstName",x).setAttribute("lastName",T).setAttribute("email",P).setAttribute("username",c).create(h).then((function(){return O("POST","/session",{username:c,password:h})})).then((function(){return S(N)})).then((function(t){return t.json()})).then((function(t){return t.data()})).then((function(t){return F.setAttribute("avatar",t)})).then((function(t){return t.update()})).then(e).catch(n)},children:"Register"})})]})},P=n(29),L=n(14),R=function(t){Object(T.a)(n,t);var e=Object(E.a)(n);function n(){return Object(m.a)(this,n),e.apply(this,arguments)}return Object(A.a)(n,[{key:"type",get:function(){return"tags"}}]),n}(C),G=function(t){Object(T.a)(n,t);var e=Object(E.a)(n);function n(){return Object(m.a)(this,n),e.apply(this,arguments)}return Object(A.a)(n,[{key:"type",get:function(){return"posts"}},{key:"create",value:function(){var t,e=this,r=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:[],i=arguments.length>2&&void 0!==arguments[2]?arguments[2]:[],c={},u=Object(s.a)(a);try{for(u.s();!(t=u.n()).done;){var o=t.value;c[o]=!1}}catch(l){u.e(l)}finally{u.f()}return O("GET","/tags?filter[text][in]=".concat(a.map((function(t){return t.getAttribute("text")})).join(","))).then((function(t){return t.json()})).then((function(t){return t.data})).then((function(t){var e,n=[],r=Object(s.a)(t);try{for(r.s();!(e=r.n()).done;){var i=e.value,u=new R(i);c[u.getAttribute("text")]=!0,n.append(u)}}catch(l){r.e(l)}finally{r.f()}var o=a.filter((function(t){return!(t.getAttribute("text")in c)}));return o.length>0?Promise.all(o.map((function(t){return O("POST","/tags",t.toResourceIdentifier())}))).then((function(t){return t.map((function(t){return new R(t.json().data)}))})).then((function(t){return n.concat(t)})):new Promise((function(t,e){return t(n)}))})).then((function(t){var e={},n=0;return null!==r&&(e.author={data:r.toResourceIdentifier()},n++),i.length>0&&(e.pets={data:i.map((function(t){return t.toResourceIdentifier()}))},n++),t.length>0&&(e.tags={data:t.map((function(t){return t.toResourceIdentifier()}))},n++),n>0?e:null})).then((function(t){return Object(P.a)(Object(L.a)(n.prototype),"create",e).call(e,t)})).then((function(t){return e.hydrate(t)}))}}]),n}(C);var N=function(t){var e=t.user,n=t.onPostCreated,a=Object(r.useState)(""),i=Object(u.a)(a,2),c=i[0],s=i[1],o=Object(r.useState)(null),l=Object(u.a)(o,2),h=l[0],j=l[1],d=Object(r.useState)(""),p=Object(u.a)(d,2),O=p[0],x=p[1],g=Object(r.useState)(""),m=Object(u.a)(g,2),A=m[0],T=m[1];return Object(v.jsxs)(y.a,{children:[Object(v.jsxs)(y.a.Group,{children:[Object(v.jsx)(y.a.Label,{children:"Title"}),Object(v.jsx)(y.a.Control,{type:"text",placeholder:"Enter post title",onChange:function(t){var e=t.target;return s(e.value)}})]}),Object(v.jsxs)(y.a.Group,{children:[Object(v.jsx)(y.a.Label,{children:"Image"}),Object(v.jsx)(y.a.File,{custom:!0,label:"Upload an image",onChange:function(t){var e=t.target;return j(e.files.item(0))}})]}),Object(v.jsxs)(y.a.Group,{children:[Object(v.jsx)(y.a.Label,{children:"Text"}),Object(v.jsx)(y.a.Control,{type:"text",placeholder:"Enter post text",onChange:function(t){var e=t.target;return x(e.value)}})]}),Object(v.jsxs)(y.a.Group,{children:[Object(v.jsx)(y.a.Label,{children:"Tags"}),Object(v.jsx)(y.a.Control,{type:"text",placeholder:"Enter commea-separated tags",onChange:function(t){var e=t.target;return T(e.value)}})]}),Object(v.jsx)(f.a,{children:Object(v.jsx)(b.a,{onClick:function(){if(null!==h){var t=A.split(",").map((function(t){return t.trim().toLowerCase()}));S(h).then((function(t){return t.json()})).then((function(t){return t.data})).then((function(t){return new G({attributes:{title:c,text:O,image:t}})})).then((function(n){return n.create(e,t,[])})).then(n)}},children:"Create"})})]})};var D=function(t){return function(e){if(e.status!==t)throw e.status;return e.json()}};var F=function(t){var e=t.onSuccess,n=t.onError,a=Object(r.useState)(""),i=Object(u.a)(a,2),c=i[0],s=i[1],o=Object(r.useState)(""),l=Object(u.a)(o,2),h=l[0],j=l[1];return Object(v.jsxs)(y.a,{children:[Object(v.jsxs)(y.a.Group,{children:[Object(v.jsx)(y.a.Label,{children:"Username"}),Object(v.jsx)(y.a.Control,{type:"text",placeholder:"Enter username",onChange:function(t){return s(t.target.value)}})]}),Object(v.jsxs)(y.a.Group,{children:[Object(v.jsx)(y.a.Label,{children:"Password"}),Object(v.jsx)(y.a.Control,{type:"password",placeholder:"Enter password",onChange:function(t){return j(t.target.value)}})]}),Object(v.jsx)(f.a,{children:Object(v.jsx)(b.a,{variant:"primary",onClick:function(){return O("POST","/session",{username:c,password:h}).then(D).then((function(t){return t.data})).then((function(t){var e=t.uid;return O("GET","/users/".concat(e))})).then((function(t){return t.json()})).then((function(t){return new k(t.data)})).then(e).catch(n)},children:"Log In"})})]})},I=["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],M=["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];var U=function(t){var e=x(t);return"".concat(I[e.getDay()],", ").concat(M[e.getMonth()]," ").concat(e.getDate(),", ").concat(e.getFullYear())};var J=function(){var t=Object(r.useState)([]),e=Object(u.a)(t,2),n=e[0],a=e[1],i=Object(r.useState)([]),c=Object(u.a)(i,2),p=c[0],x=c[1],y=Object(r.useState)(null),m=Object(u.a)(y,2),A=m[0],T=m[1],E=function(){return O("GET","/users").then((function(t){return t.json()})).then((function(t){return t.data})).then((function(t){return t.map((function(t){return new k(t)}))})).then(a)},C=function(){return O("GET","/posts?include=author,tags&fields[users]=username").then((function(t){return t.json()})).then((function(t){var e,n=t.data,r=t.included,a=[],i=Object(s.a)(n);try{var c=function(){var t=e.value,n=t.attributes,i=t.relationships,c={tags:i.tags.data.map((function(t){return t.id})),author:i.author.data.id};a.push({image:n.image,title:n.title,text:n.text,createdAt:U(n.createdAt),author:r.find((function(t){return"users"===t.type&&t.id===c.author})).attributes.username,tags:r.filter((function(t){return"tags"===t.type&&c.tags.includes(t.id)})).map((function(t){return"#".concat(t.attributes.text)}))})};for(i.s();!(e=i.n()).done;)c()}catch(u){i.e(u)}finally{i.f()}return a})).then(x)};return Object(r.useEffect)((function(){E(),C(),O("GET","/session").then((function(t){return t.json()})).then((function(t){return t.data.uid})).then((function(t){return O("GET","/users/".concat(t)).then((function(t){return t.json()})).then((function(t){return new k(t.data)}))})).then(T).catch((function(t){return console.log("Not logged in")}))}),[T]),Object(v.jsxs)(o.a,{children:[Object(v.jsx)("h1",{children:"Users and Authentication"}),Object(v.jsx)(b.a,{className:"mb-4",onClick:E,children:"Refresh"}),Object(v.jsxs)(d.a,{children:[Object(v.jsx)("thead",{children:Object(v.jsxs)("tr",{children:[Object(v.jsx)("th",{children:"ID"}),Object(v.jsx)("th",{children:"Username"}),Object(v.jsx)("th",{children:"E-mail Address"}),Object(v.jsx)("th",{children:"First Name"}),Object(v.jsx)("th",{children:"Last Name"}),Object(v.jsx)("th",{children:"Joined On"})]})}),Object(v.jsx)("tbody",{children:n.map((function(t,e){return Object(v.jsxs)("tr",{children:[Object(v.jsx)("td",{children:t.id}),Object(v.jsx)("td",{children:t.getAttribute("username")}),Object(v.jsx)("td",{children:t.getAttribute("email")}),Object(v.jsx)("td",{children:t.getAttribute("firstName")}),Object(v.jsx)("td",{children:t.getAttribute("lastName")}),Object(v.jsx)("td",{children:t.getAttribute("createdAt")})]},e)}))})]}),A?Object(v.jsx)(g,{user:A}):Object(v.jsx)(F,{onSuccess:T,onError:function(t){return window.alert("Can't log in! (error: ".concat(t,")"))}}),Object(v.jsx)("p",{children:"Don't have an account? Create one!"}),Object(v.jsx)(w,{onRegistered:T,onError:console.error}),Object(v.jsx)("hr",{}),Object(v.jsx)("h1",{children:"Posts"}),Object(v.jsx)(f.a,{children:Object(v.jsx)(b.a,{onClick:function(){return C()},children:"Refresh"})}),Object(v.jsx)(l.a,{children:p.map((function(t){return Object(v.jsx)(h.a,{xs:1,sm:2,md:3,lg:4,children:Object(v.jsxs)(j.a,{children:[Object(v.jsx)(j.a.Img,{src:t.image}),Object(v.jsxs)(j.a.Body,{children:[Object(v.jsx)(j.a.Title,{children:t.title}),Object(v.jsxs)(j.a.Text,{children:[Object(v.jsxs)("small",{className:"text-muted",children:["Posted By ",p.author," on ",p.createdAt]}),Object(v.jsx)("p",{children:t.text}),Object(v.jsxs)("p",{className:"text-muted",children:["Tags: ",t.tags.join(", ")]})]})]})]})})}))}),A&&Object(v.jsxs)(v.Fragment,{children:[Object(v.jsx)("h3",{children:"Create Post"}),Object(v.jsx)(N,{user:A,onPostCreated:function(){return C()}})]})]})};n(38);c.a.render(Object(v.jsx)(a.a.StrictMode,{children:Object(v.jsx)(J,{})}),document.getElementById("root"))}},[[39,1,2]]]);
//# sourceMappingURL=main.ebea5b1a.chunk.js.map