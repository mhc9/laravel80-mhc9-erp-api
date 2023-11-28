import React from 'react';
import ReactDOM from 'react-dom';
import { HashRouter, Routes, Route } from 'react-router-dom';

function App() {
    return (
        <Routes>
            <Route path="/" element={<div>Home</div>} />
            <Route path="*" element={<div>Not Found</div>} />
        </Routes>
    );
}

export default App;

if (document.getElementById('root')) {
    ReactDOM.render(
        <HashRouter>
            <App />
        </HashRouter>,
        document.getElementById('root')
    );
}
