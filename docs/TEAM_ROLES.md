# Team Roles and Responsibilities

## Team Member 1: Backend Lead

### Core Responsibilities
- Build puzzle logic engine (solvability check, shuffling, valid moves)
- Build API endpoints:
  - `/api/login`
  - `/api/register`
  - `/api/start-session`
  - `/api/update-session`
  - `/api/analytics`
- Implement difficulty scaling + hint system
- Implement holiday power-ups logic
- Security:
  - Password hashing
  - Prepared statements
  - SQL injection prevention

### Deliverables
- `backend/` folder
- Full game logic implemented
- Working API (Node or PHP)
- JSON responses for frontend
- Difficulty + hint algorithms

### Files Owned
- `backend/server.js` or `backend/index.php`
- `backend/routes/`
- `backend/controllers/`
- `backend/utils/puzzleLogic.js`
- `backend/utils/difficulty.js`
- `backend/utils/hints.js`
- `backend/utils/powerups.js`
- `backend/utils/security.js`

## Team Member 2: Frontend UI/UX Lead

### Core Responsibilities
- Build the entire puzzle board UI (HTML/CSS/JS or React)
- Tile movement + slide animations
- Festive theme system:
  - Snow overlay
  - Color palette swap
  - Optional day/night cycle
- Christmas Story Mode UI:
  - Intro screen
  - Character/story text panels
- Visuals for:
  - Power-ups
  - Rewards
  - Completion celebration

### Deliverables
- `/frontend/` folder
- Interactive puzzle grid
- All visual animations
- Theme switching system
- Story mode visuals

### Files Owned
- `frontend/index.html`
- `frontend/css/`
- `frontend/js/` (except api-client.js)
- `frontend/assets/`
- `frontend/components/` (if using React)

## Team Member 3: Database + Analytics + Game Progress Features

### Core Responsibilities
- Create MySQL schema (CLI ONLY)
- Create database tables:
  - users
  - game_sessions
  - puzzles
  - analytics
  - preferences (optional)
- Write SQL migration script
- Track analytics:
  - moves
  - completion time
  - difficulty levels
  - power-up usage
- Build player dashboard ("Your Stats" page)
- Implement Gift & Reward System:
  - badges
  - achievements
  - backend triggers + frontend data pass

### Deliverables
- `db/schema.sql`
- CLI-created database
- Analytics API endpoints
- Dashboard UI pulling analytics
- Rewards + badges system backend

### Files Owned
- `database/` folder
- `backend/controllers/analyticsController.js`
- `backend/models/` (database models)
- Dashboard UI components (coordinate with Frontend Lead)

## Collaboration Points

### Shared Files
- `frontend/js/api-client.js` - Both Frontend and Backend teams
- `backend/controllers/analyticsController.js` - Backend and Database teams
- `docs/` - All team members contribute

### Communication
- Daily standups to sync progress
- Code reviews before merging
- Shared documentation in `docs/` folder

## Git Workflow

1. Each team member works on their feature branch
2. Regular commits with clear messages
3. Pull requests for code review
4. Merge to main after approval

