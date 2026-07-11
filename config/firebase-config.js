// Firebase Configuration
const firebaseConfig = {
  apiKey: "AIzaSyAAN0rK7wzORzeUP0p_W8aqei1hfINwX_k",
  authDomain: "ledger-go.firebaseapp.com",
  projectId: "ledger-go",
  storageBucket: "ledger-go.firebasestorage.app",
  messagingSenderId: "797091501991",
  appId: "1:797091501991:web:e84ffe6dd3c9a6fbb0c882",
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);
const auth = firebase.auth();

// Persistence will be set dynamically based on Remember Me checkbox
